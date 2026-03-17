<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Use tenant login when tenant is the current context, otherwise central login.
        if (function_exists('tenant') && tenant()) {
            return Route::has('tenant.login') ? route('tenant.login') : route('central.login');
        }

        return Route::has('central.login') ? route('central.login') : (Route::has('login') ? route('login') : null);
    }
}
