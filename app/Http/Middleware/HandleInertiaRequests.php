<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $guard = tenant() ? 'tenant' : 'central';

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'app_type' => $guard,
            'auth' => [
                'user' => $request->user($guard),
                'customer' => tenant() ? $request->user('customer') : null,
            ],
            'tenant' => tenant(),
            'cart_count' => $this->cartCount($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    private function cartCount(Request $request): int
    {
        if (! tenant() || ! $request->user('customer')) {
            return 0;
        }

        $cart = $request->user('customer')->cart;

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }
}
