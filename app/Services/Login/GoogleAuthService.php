<?php

namespace App\Services\Login;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;

class GoogleAuthService
{
    public function login(Request $request)
    {
        try {
            // Fetch user data from Google API
            $response = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'access_token' => $request->access_token,
            ]);

            if ($response->failed() || !isset($response['email'])) {
                return response()->json([
                    'error' => 'Invalid access token or Google API error.',
                ], 400);
            }

            $userData = $response->json();
            $user = User::where('email', $userData['email'])->first();

            if (!$user) {
                // Register the user if they don't exist
                $user = User::create([
                    'name' => $userData['name'] ?? explode('@', $userData['email'])[0],
                    'email' => $userData['email'],
                    'password' => Hash::make(Str::random(16)), // Random password
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update(['email_verified_at'=> now()]);
            }

            // Authenticate the user
            Auth::login($user);

            // Check 2FA
            if ($user->hasTwoFactorEnabled()) {
                $preAuthToken = JWTAuth::claims([
                    '2fa_pending' => true,
                    'sub' => $user->id,
                    'email' => $user->email,
                ])->fromUser($user);

                logUserActivity(
                    activity: 'Google Login 2FA Challenge Triggered',
                    category: 'Authentication',
                    userId: $user->id,
                    request: $request,
                    isSuccess: true
                );

                return response()->json([
                    'message' => 'Two-factor authentication required.',
                    '2fa_required' => true,
                    'pre_auth_token' => $preAuthToken,
                ], 200);
            }

            // Payload for JWT (can be used for additional claims if needed)
            $userPayload = [
                'email' => $user->email,
                'name' => $user->name,
                'category' => $user->category ?? 'default',
                'email_verified' => $user->hasVerifiedEmail(),
            ];

            try {
                $token = JWTAuth::fromUser($user, ['guard' => 'user']);
            } catch (JWTException $e) {
                return response()->json([
                    'error' => 'Could not create JWT token',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'status_code' => 200,
                'token' => $token,
                'access_token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_verified' => true,
                    'role' => $user->role ?? 'user',
                    'status' => $user->status ?? 'active',
                    'email_verified' => true,
                    'profile_picture' => $userData['picture'] ?? null,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred during Google authentication.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
