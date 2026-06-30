<?php

namespace App\Http\Controllers\Auth\User;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use App\Mail\OtpNotification;
use App\Http\Requests\Auth\SendResetLinkRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;

class UserPasswordResetController extends Controller
{
    /**
     * Send OTP for password reset.
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => ['errMsg' => 'Email not found or invalid']], 400);
        }

        $user = User::where('email', $request->email)->first();

        // Generate a 6-digit OTP
        $otp = random_int(100000, 999999);
        $user->otp = Hash::make($otp);
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            Mail::to($user->email)->send(new OtpNotification($otp));
            return response()->json([
                'status_code' => 200,
                'message' => 'OTP sent to your email.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Forgot Password OTP send failed: ' . $e->getMessage());
            return response()->json(['error' => ['errMsg' => 'Failed to send OTP email. Please try again.']], 500);
        }
    }

    /**
     * Verify OTP for password reset.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => ['errMsg' => 'Invalid inputs']], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->otp || !$user->otp_expires_at || $user->otp_expires_at < now()) {
            return response()->json(['error' => ['errMsg' => 'OTP has expired or is invalid']], 400);
        }

        if (Hash::check($request->otp, $user->otp)) {
            return response()->json([
                'status_code' => 200,
                'message' => 'OTP verified successfully.'
            ], 200);
        }

        return response()->json(['error' => ['errMsg' => 'Invalid OTP']], 400);
    }

    /**
     * Reset password using OTP.
     */
    public function resetWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|min:6|max:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => ['errMsg' => $validator->errors()->first()]], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->otp || !$user->otp_expires_at || $user->otp_expires_at < now()) {
            return response()->json(['error' => ['errMsg' => 'OTP has expired or is invalid']], 400);
        }

        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json(['error' => ['errMsg' => 'Invalid OTP']], 400);
        }

        // Reset password
        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'status_code' => 200,
            'message' => 'Password reset successfully.'
        ], 200);
    }

    /**
     * Send a password reset link to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function sendResetLinkEmail(SendResetLinkRequest $request)
    {

        $email = $request->input('email');
        $resetUrlBase = $request->input('redirect_url');

        // Find the user by email
        $user = User::where('email', $email)->first();

        // Send the password reset link
        $response = Password::sendResetLink(
            $request->only('email'),
            function ($user, $token) use ($resetUrlBase) {
                // Create the full reset URL
                $resetUrl = "{$resetUrlBase}?token={$token}&email={$user->email}";

                // Send the email
                Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));
            }
        );

        // Return response based on whether the reset link was sent
        if ($response == Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => __($response),
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 200);
        } else {
            return response()->json(['error' => __($response)], 400);
        }
    }



    /**
     * Reset the user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(ResetPasswordRequest $request)
    {

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $response == Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password has been reset successfully.'])
            : response()->json(['error' => 'Unable to reset password.'], 500);
    }
}
