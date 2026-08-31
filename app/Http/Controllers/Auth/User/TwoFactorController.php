<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginRegisterUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Tymon\JWTAuth\Facades\JWTAuth;

class TwoFactorController extends Controller
{
    /**
     * Get current 2FA status for the authenticated user.
     */
    public function status(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'enabled' => $user->hasTwoFactorEnabled(),
            'confirmed_at' => $user->two_factor_confirmed_at,
            'recovery_codes' => $user->two_factor_recovery_codes ?? [],
            'recovery_codes_count' => count($user->two_factor_recovery_codes ?? []),
        ]);
    }

    /**
     * Setup 2FA: Generate secret key and QR Code (SVG Base64).
     */
    public function setup(Request $request)
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        // Generate a new secret key
        $secret = $google2fa->generateSecretKey();

        // Build QR Code URL
        $appName = config('app.name', 'goldenmark.money');
        if ($appName === 'Laravel') {
            $appName = 'goldenmark.money';
        }
        $qrCodeUrl = $google2fa->getQRCodeUrl($appName, $user->email, $secret);

        // Render QR Code as SVG Base64 string
        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $svgContent = $writer->writeString($qrCodeUrl);
        $qrCodeSvg = 'data:image/svg+xml;base64,' . base64_encode($svgContent);

        // Save secret temporarily (pending confirmation)
        $user->google2fa_secret = $secret;
        $user->save();

        logUserActivity(
            activity: '2FA Setup Requested',
            category: 'Security',
            userId: $user->id,
            request: $request,
            isSuccess: true
        );

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'qr_code_svg' => $qrCodeSvg,
            'qr_code_svg_raw' => $svgContent,
            'qr_code_image_url' => 'https://quickchart.io/qr?text=' . urlencode($qrCodeUrl) . '&size=220',
            'message' => 'Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.)'
        ]);
    }

    /**
     * Confirm and Enable 2FA with 6-digit OTP code.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = Auth::user();

        if (empty($user->google2fa_secret)) {
            return response()->json(['message' => 'Please setup 2FA first before enabling.'], 400);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            logUserActivity(
                activity: '2FA Enable Failed',
                category: 'Security',
                userId: $user->id,
                request: $request,
                isSuccess: false,
                extraDetails: ['reason' => 'Invalid OTP code']
            );

            return response()->json(['message' => 'Invalid 6-digit code. Please try again.'], 422);
        }

        // Generate 8 single-use recovery codes
        $recoveryCodes = collect(range(1, 8))->map(function () {
            return Str::random(5) . '-' . Str::random(5);
        })->toArray();

        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_confirmed_at = now();
        $user->two_factor_verification = true;
        $user->save();

        logUserActivity(
            activity: '2FA Enabled Successfully',
            category: 'Security',
            userId: $user->id,
            request: $request,
            isSuccess: true
        );

        return response()->json([
            'message' => 'Two-Factor Authentication is now enabled on your account.',
            'recovery_codes' => $recoveryCodes
        ]);
    }

    /**
     * Verify 2FA challenge during login.
     */
    public function verifyLogin(Request $request)
    {
        $request->validate([
            'pre_auth_token' => 'required|string',
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        if (empty($request->code) && empty($request->recovery_code)) {
            return response()->json(['message' => 'Please provide either a 6-digit code or a recovery code.'], 422);
        }

        // Validate Pre-Auth JWT Token
        try {
            $payload = JWTAuth::setToken($request->pre_auth_token)->getPayload();

            if (!$payload->get('2fa_pending')) {
                return response()->json(['message' => 'Invalid authentication state.'], 401);
            }

            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user || $user->is_blocked || !$user->is_active) {
                return response()->json(['message' => 'User account is invalid or inactive.'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentication challenge session expired or invalid. Please login again.'], 401);
        }

        // Anti-Replay Attack Protection
        if ($request->code) {
            $cacheKey = "2fa_used_{$user->id}_{$request->code}";
            if (Cache::has($cacheKey)) {
                return response()->json(['message' => 'This verification code was already used. Please wait for the next code.'], 422);
            }
        }

        $google2fa = new Google2FA();
        $isVerified = false;

        if (!empty($request->code)) {
            $isVerified = $google2fa->verifyKey($user->google2fa_secret, $request->code);
            if ($isVerified) {
                Cache::put("2fa_used_{$user->id}_{$request->code}", true, 60);
            } else {
                logUserActivity(
                    activity: '2FA Login Challenge Failed',
                    category: 'Authentication',
                    userId: $user->id,
                    request: $request,
                    isSuccess: false,
                    extraDetails: ['reason' => 'Invalid 6-digit OTP code']
                );
                return response()->json(['message' => 'Invalid 6-digit verification code. Please try again.'], 422);
            }
        } elseif (!empty($request->recovery_code)) {
            $submittedCode = trim($request->recovery_code);
            $codes = $user->two_factor_recovery_codes ?? [];

            if (in_array($submittedCode, $codes)) {
                $isVerified = true;
                // Remove used recovery code (Single-use protection)
                $user->two_factor_recovery_codes = array_values(array_diff($codes, [$submittedCode]));
                $user->save();
            } else {
                logUserActivity(
                    activity: '2FA Recovery Code Used/Invalid Failed',
                    category: 'Authentication',
                    userId: $user->id,
                    request: $request,
                    isSuccess: false,
                    extraDetails: ['reason' => 'Used or invalid recovery code']
                );
                return response()->json([
                    'message' => 'This recovery code is invalid or has already been used. Please use an unused recovery code or your 6-digit authenticator code.'
                ], 422);
            }
        }

        if (!$isVerified) {
            return response()->json(['message' => 'Invalid 2FA verification challenge.'], 422);
        }

        // Update last login
        $user->last_login_at = now();
        $user->save();

        // Issue Full Access JWT Token
        try {
            $token = JWTAuth::fromUser($user, ['guard' => 'user']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not create access token'], 500);
        }

        logUserActivity(
            activity: '2FA Login Verification Successful',
            category: 'Authentication',
            userId: $user->id,
            request: $request,
            isSuccess: true
        );

        return response()->json(new LoginRegisterUserResource($user, $token), 200);
    }

    /**
     * Disable 2FA (Requires current password + 6-digit TOTP code).
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect password.'], 400);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            return response()->json(['message' => 'Invalid 6-digit code.'], 422);
        }

        $user->google2fa_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_verification = false;
        $user->save();

        logUserActivity(
            activity: '2FA Disabled',
            category: 'Security',
            userId: $user->id,
            request: $request,
            isSuccess: true
        );

        return response()->json(['message' => 'Two-Factor Authentication has been disabled.']);
    }

    /**
     * Regenerate Recovery Codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json(['message' => 'Two-Factor Authentication is not enabled.'], 400);
        }

        $recoveryCodes = collect(range(1, 8))->map(function () {
            return Str::random(5) . '-' . Str::random(5);
        })->toArray();

        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->save();

        logUserActivity(
            activity: '2FA Recovery Codes Regenerated',
            category: 'Security',
            userId: $user->id,
            request: $request,
            isSuccess: true
        );

        return response()->json([
            'message' => 'New recovery codes generated.',
            'recovery_codes' => $recoveryCodes,
        ]);
    }
}
