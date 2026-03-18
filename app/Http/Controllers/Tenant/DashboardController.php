<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('tenant/Dashboard', [
            'productsTotal' => Product::count(),
            'productsActive' => Product::where('active', true)->count(),
            'productsInactive' => Product::where('active', false)->count(),
            'customersTotal' => Customer::count(),
        ]);
    }
}
