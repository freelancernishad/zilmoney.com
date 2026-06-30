<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\AttachJwtFromCookie;
use App\Http\Controllers\Common\SystemSettings\SystemSettingController;

Route::prefix('admin')->group(function () {
    Route::middleware([AttachJwtFromCookie::class, AuthenticateAdmin::class])->group(function () {
        Route::get('/system-setting', [SystemSettingController::class, 'index']);
        Route::post('/system-setting', [SystemSettingController::class, 'storeOrUpdate']);
    });
});
