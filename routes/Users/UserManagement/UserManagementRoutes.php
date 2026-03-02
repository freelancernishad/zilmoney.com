<?php


use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Controllers\User\UserManagement\UserController;



Route::prefix('/user')->group(function () {
    Route::middleware(AuthenticateUser::class)->group(function () { // Applying user middleware
        // Get logged-in user profile
        Route::get('profile', [UserController::class, 'profile']);

        // Update logged-in user profile
        Route::put('profile', [UserController::class, 'update']);

        // Update Personal Info
        Route::put('personal-info', [UserController::class, 'updatePersonalInfo']);

        // Update Business Detail
        Route::put('business-detail', [UserController::class, 'updateBusinessDetail']);

        Route::post('profile-picture', [UserController::class, 'updateProfilePicture']);

        Route::post('photos', [UserController::class, 'updatePhotos']);
        Route::post('photos/set-primary', [UserController::class, 'setPrimaryPhoto']);

        // Document Management
        Route::get('documents', [\App\Http\Controllers\User\UserManagement\UserDocumentController::class, 'index']);
        Route::post('documents', [\App\Http\Controllers\User\UserManagement\UserDocumentController::class, 'store']);
    });
});
