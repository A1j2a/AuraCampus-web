<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('register', [AuthController::class, 'register']);

    Route::get('login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('login', [AuthController::class, 'login']);

    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');

    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');

    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [AuthController::class, 'showVerifyEmailPrompt'])
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [AuthController::class, 'sendEmailVerificationNotification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [AuthController::class, 'showConfirmPassword'])
        ->name('password.confirm');

    Route::post('confirm-password', [AuthController::class, 'confirmPassword']);

    Route::put('password', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::post('logout', [AuthController::class, 'logout'])
        ->name('logout');
});
