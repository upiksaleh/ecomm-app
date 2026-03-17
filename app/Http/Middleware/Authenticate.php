<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Handle unauthenticated users — detects which guard failed
     * so we can redirect to the correct login page.
     */
    protected function unauthenticated($request, array $guards): void
    {
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->resolveRedirect($request, $guards),
        );
    }

    /**
     * Resolve the correct redirect URL based on the failing guard.
     */
    protected function resolveRedirect(Request $request, array $guards): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Customer guard → customer login
        if (in_array('customer', $guards)) {
            return Route::has('tenant.customer.login')
                ? route('tenant.customer.login')
                : null;
        }

        // Tenant context (admin) → tenant login
        if (function_exists('tenant') && tenant()) {
            return Route::has('tenant.login')
                ? route('tenant.login')
                : route('central.login');
        }

        // Central context → central login
        return Route::has('central.login')
            ? route('central.login')
            : (Route::has('login') ? route('login') : null);
    }

    /**
     * Keep redirectTo in sync for any code that calls it directly.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $this->resolveRedirect($request, []);
    }
}
