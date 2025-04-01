<?php

use Illuminate\Support\Facades\Route;
use Ginkelsoft\Buildora\Http\Controllers\Auth\LoginController;
use Ginkelsoft\Buildora\Http\Controllers\Auth\ForgotPasswordController;
use Ginkelsoft\Buildora\Http\Controllers\Auth\ResetPasswordController;

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
