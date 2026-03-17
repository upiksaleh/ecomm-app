<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function cart(): Cart
    {
        $customer = Auth::guard('customer')->user();

        return $customer->cart ?? Cart::create(['customer_id' => $customer->id]);
    }

    public function index()
    {
        $cart = $this->cart()->load('items.product');

        return inertia('tenant/cart/Index', [
            'cart' => [
                'items' => $cart->items->map(fn ($item) => [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'sku' => $item->product->sku,
                        'image' => null,
                    ],
                ]),
                'total' => $cart->total,
            ],
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100',
        ]);

        $product = Product::findOrFail($data['product_id']);

        abort_if(! $product->active, 422, 'Product is not available.');

        $cart = $this->cart();

        $existing = $cart->items()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->increment('quantity', $data['quantity'] ?? 1);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $data['quantity'] ?? 1,
                'price' => $product->price,
            ]);
        }

        return back()->with('success', 'Product added to cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($cartItem);

        $data = $request->validate(['quantity' => 'required|integer|min:1|max:100']);

        $cartItem->update(['quantity' => $data['quantity']]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($cartItem);

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }

    private function authorizeCartItem(CartItem $cartItem): void
    {
        $cart = $this->cart();
        abort_if($cartItem->cart_id !== $cart->id, 403);
    }
}
