<?php

use Illuminate\Support\Facades\Route;
use Ginkelsoft\Buildora\Http\Controllers\Auth\LoginController;
use Ginkelsoft\Buildora\Http\Controllers\Auth\ForgotPasswordController;
use Ginkelsoft\Buildora\Http\Controllers\Auth\ResetPasswordController;
use Ginkelsoft\Buildora\Http\Controllers\InstallController;
use Ginkelsoft\Buildora\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Buildora Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle user authentication for the Buildora admin interface.
| They include login, logout, and password reset functionality.
|
| Route prefix: /buildora/auth
| Middleware:   web
|
*/

Route::middleware('web')
    ->prefix('buildora')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Installation Routes
        |--------------------------------------------------------------------------
        |
        | Show the installation wizard and handle the installation process.
        |
        */
        Route::get('install', [InstallController::class, 'index'])
            ->name('buildora.install'); // GET: Installation wizard

        Route::post('install', [InstallController::class, 'process'])
            ->name('buildora.install.process'); // POST: Process installation

        /*
        |--------------------------------------------------------------------------
        | Asset Routes
        |--------------------------------------------------------------------------
        |
        | Serve package assets like logo without needing to publish them.
        |
        */
        Route::get('assets/{file}', function ($file) {
            $path = __DIR__ . '/../resources/assets/' . $file;

            if (!file_exists($path)) {
                abort(404);
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimeTypes = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
            ];

            return response()->file($path, [
                'Content-Type' => $mimeTypes[$extension] ?? 'application/octet-stream',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        })->where('file', '.*')->name('buildora.asset');
    });

Route::middleware('web')
    ->prefix('buildora/auth')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Login Routes
        |--------------------------------------------------------------------------
        |
        | Display the login form, handle login submission, and logout.
        |
        */
        Route::get('login', [LoginController::class, 'showLoginForm'])
            ->name('buildora.login'); // GET: Login form

        Route::post('login', [LoginController::class, 'login'])
            ->name('buildora.login.post'); // POST: Handle login attempt

        Route::post('logout', [LoginController::class, 'logout'])
            ->name('buildora.logout'); // POST: Logout current user

        /*
        |--------------------------------------------------------------------------
        | Two-Factor Authentication Challenge Routes
        |--------------------------------------------------------------------------
        |
        | Show 2FA challenge and verify code during login.
        |
        */
        Route::get('two-factor/challenge', [TwoFactorController::class, 'challenge'])
            ->name('buildora.two-factor.challenge'); // GET: 2FA challenge form

        Route::post('two-factor/challenge', [TwoFactorController::class, 'verify'])
            ->name('buildora.two-factor.verify'); // POST: Verify 2FA code

        /*
        |--------------------------------------------------------------------------
        | Forgot Password Routes
        |--------------------------------------------------------------------------
        |
        | Show the password reset request form and handle the email submission.
        |
        */
        Route::get('password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('buildora.password.request'); // GET: Forgot password form

        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('buildora.password.email'); // POST: Send password reset email


        /*
        |--------------------------------------------------------------------------
        | Password Reset Routes
        |--------------------------------------------------------------------------
        |
        | Show the password reset form with token and handle new password submission.
        |
        */
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
            ->name('buildora.password.reset'); // GET: Reset form with token

        Route::post('password/reset', [ResetPasswordController::class, 'reset'])
            ->name('buildora.password.update'); // POST: Save new password
    });
