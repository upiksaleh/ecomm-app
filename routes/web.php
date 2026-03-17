<?php

use App\Http\Controllers\Central\AuthController;
use App\Http\Middleware\Authenticate as CentralAuthenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;

Route::aliasMiddleware('auth', CentralAuthenticate::class);
Route::aliasMiddleware('guest', RedirectIfAuthenticated::class);
Route::aliasMiddleware('verified', EnsureEmailIsVerified::class);

foreach (config('tenancy.central_domains') as $domain) {
    Route::name('central.')->group(function () use ($domain) {
        Route::domain($domain)->group(function () {

            Route::inertia('/', 'central/Home')->name('home');

            Route::middleware(['guest'])->group(function () {

                Route::post('/login', [AuthController::class, 'login'])->name('login_store');
                Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

            });

            Route::middleware(['auth:central', 'verified'])->group(function () {
                Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

                Route::inertia('/dashboard', 'central/Dashboard')->name('dashboard');
            });
        });
    });
}
