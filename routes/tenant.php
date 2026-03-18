<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\CustomerAuthController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ShopController;
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

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Tenant product management CRUD
            Route::resource('products', ProductController::class)->names('products');
        });

        // Public shop
        Route::name('shop.')->group(function () {
            Route::get('/shop', [ShopController::class, 'index'])->name('index');
            Route::get('/shop/{product}', [ShopController::class, 'show'])->name('show');
        });

        // Customer authentication
        Route::name('customer.')->group(function () {
            Route::middleware('guest:customer')->group(function () {
                Route::get('/customer/login', [CustomerAuthController::class, 'showLogin'])->name('login');
                Route::post('/customer/login', [CustomerAuthController::class, 'login'])->name('login_store');
                Route::get('/customer/register', [CustomerAuthController::class, 'showRegister'])->name('register');
                Route::post('/customer/register', [CustomerAuthController::class, 'register'])->name('register_store');
            });

            Route::middleware('auth:customer')->group(function () {
                Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('logout');
            });
        });

        // Cart (requires customer login)
        Route::name('cart.')->middleware('auth:customer')->group(function () {
            Route::get('/cart', [CartController::class, 'index'])->name('index');
            Route::post('/cart', [CartController::class, 'add'])->name('add');
            Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('update');
            Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('remove');
        });
    });
});
