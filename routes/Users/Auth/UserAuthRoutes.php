<?php


use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Controllers\Auth\User\AuthUserController;
use App\Http\Controllers\Auth\User\VerificationController;
use App\Http\Controllers\Auth\User\UserPasswordResetController;


Route::prefix('auth/user')->group(function () {
    Route::post('login', [AuthUserController::class, 'login'])->name('login');
    Route::post('register', [AuthUserController::class, 'register']);

    Route::middleware(AuthenticateUser::class)->group(function () { // Applying user middleware
        Route::post('logout', [AuthUserController::class, 'logout']);
        Route::get('me', [AuthUserController::class, 'me']);
        Route::post('change-password', [AuthUserController::class, 'changePassword']);
        Route::get('check-token', [AuthUserController::class, 'checkToken']);
    });
});


// Password reset routes
Route::post('user/password/email', [UserPasswordResetController::class, 'sendResetLinkEmail']);
Route::post('user/password/reset', [UserPasswordResetController::class, 'reset']);
Route::post('user/password/forgot-otp', [UserPasswordResetController::class, 'sendOtp']);
Route::post('user/password/verify-otp', [UserPasswordResetController::class, 'verifyOtp']);
Route::post('user/password/reset-otp', [UserPasswordResetController::class, 'resetWithOtp']);

Route::post('/verify-otp', [VerificationController::class, 'verifyOtp']);
Route::post('/resend/otp', [VerificationController::class, 'resendOtp']);
Route::get('/email/verify/{hash}', [VerificationController::class, 'verifyEmail']);
Route::post('/resend/verification-link', [VerificationController::class, 'resendVerificationLink']);

// Google and Apple OAuth routes (supports all URL variations)
Route::post('auth/google/login', [AuthUserController::class, 'googleLogin']);
Route::post('auth/user/google-login', [AuthUserController::class, 'googleLogin']);
Route::post('auth/user/google/login', [AuthUserController::class, 'googleLogin']);

Route::post('auth/apple/login', [AuthUserController::class, 'appleLogin']);
Route::post('auth/user/apple-login', [AuthUserController::class, 'appleLogin']);
Route::post('auth/user/apple/login', [AuthUserController::class, 'appleLogin']);


