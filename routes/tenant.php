<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::name('tenant.')->group(function () {
        Route::inertia('/', 'tenant/Home')->name('home');
        Route::middleware(['guest'])->group(function () {
            Route::post('/login', [AuthController::class, 'login'])->name('login_store');
            Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        });
        Route::middleware(['auth:tenant', 'verified'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

            Route::inertia('/dashboard', 'tenant/Dashboard')->name('dashboard');
        });
    });
});
