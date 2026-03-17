<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;

class ShopController extends Controller
{
    public function index()
    {
        $products = Product::where('active', true)
            ->orderBy('name')
            ->paginate(12);

        return inertia('tenant/shop/Index', compact('products'));
    }

    public function show(Product $product)
    {
        abort_if(! $product->active, 404);

        return inertia('tenant/shop/Show', compact('product'));
    }
}
