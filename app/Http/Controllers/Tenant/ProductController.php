<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(15);

        return inertia('tenant/products/Index', compact('products'));
    }

    public function create()
    {
        return inertia('tenant/products/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $data['active'] = $request->boolean('active', false);

        Product::create($data);

        return redirect()->route('tenant.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return inertia('tenant/products/Show', compact('product'));
    }

    public function edit(Product $product)
    {
        return inertia('tenant/products/Edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,'.$product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);
        $data['active'] = $request->boolean('active', false);

        $product->update($data);

        return redirect()->route('tenant.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('tenant.products.index')->with('success', 'Product deleted successfully.');
    }
}
