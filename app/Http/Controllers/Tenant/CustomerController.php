<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $customers = Customer::query()
            ->search($search)
            ->sortBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return inertia('tenant/customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function show(Customer $customer): Response
    {
        // Eager load cart relationship with items
        $customer->load(['cart.items']);

        // Calculate cart status
        $cart = $customer->cart;
        $cartStatus = [
            'hasActiveCart' => $cart !== null,
            'itemCount' => $cart ? $cart->items->count() : 0,
        ];

        return inertia('tenant/customers/Show', [
            'customer' => $customer,
            'cartStatus' => $cartStatus,
        ]);
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        try {
            // Delete customer (cascade will handle cart and cart items)
            $customer->delete();

            return redirect()
                ->route('tenant.customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Failed to delete customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Unable to delete customer. Please try again.');
        }
    }
}
