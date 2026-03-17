<?php

use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use Tests\Traits\CreatesTestTenants;

uses(CreatesTestTenants::class);

// ---------------------------------------------------------------------------
// Cart::getTotalAttribute
// ---------------------------------------------------------------------------

test('cart total is zero when there are no items', function () {
    $tenant = $this->createTenant('cart-unit-1', 'cart-unit-1.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        expect($cart->total)->toBe(0.0);
    });
});

test('cart total sums price times quantity for a single item', function () {
    $tenant = $this->createTenant('cart-unit-2', 'cart-unit-2.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->priced(25.00)->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 25.00,
        ]);

        $cart->load('items');

        expect($cart->total)->toBe(75.0);
    });
});

test('cart total sums all items correctly', function () {
    $tenant = $this->createTenant('cart-unit-3', 'cart-unit-3.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        $productA = Product::factory()->priced(10.00)->create();
        $productB = Product::factory()->priced(5.50)->create();

        CartItem::create(['cart_id' => $cart->id, 'product_id' => $productA->id, 'quantity' => 2, 'price' => 10.00]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $productB->id, 'quantity' => 4, 'price' => 5.50]);

        $cart->load('items');

        // (10 * 2) + (5.5 * 4) = 20 + 22 = 42
        expect($cart->total)->toBe(42.0);
    });
});

test('cart total uses the stored price snapshot, not the current product price', function () {
    $tenant = $this->createTenant('cart-unit-4', 'cart-unit-4.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->priced(100.00)->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        // Cart item was added when price was 100.
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100.00,
        ]);

        // Product price changes after item was added.
        $product->update(['price' => 999.00]);

        $cart->load('items');

        // Total should still reflect snapshot price (100), not new price (999).
        expect($cart->total)->toBe(100.0);
    });
});

// ---------------------------------------------------------------------------
// CartItem::getSubtotalAttribute
// ---------------------------------------------------------------------------

test('cart item subtotal is price times quantity', function () {
    $tenant = $this->createTenant('cart-unit-5', 'cart-unit-5.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->priced(12.50)->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 6,
            'price' => 12.50,
        ]);

        expect($item->subtotal)->toBe(75.0);
    });
});

test('cart item subtotal with quantity of one equals price', function () {
    $tenant = $this->createTenant('cart-unit-6', 'cart-unit-6.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->priced(49.99)->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 49.99,
        ]);

        expect($item->subtotal)->toBe(49.99);
    });
});

// ---------------------------------------------------------------------------
// Relationships
// ---------------------------------------------------------------------------

test('cart belongs to a customer', function () {
    $tenant = $this->createTenant('cart-unit-7', 'cart-unit-7.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        expect($cart->customer->id)->toBe($customer->id);
    });
});

test('customer has one cart', function () {
    $tenant = $this->createTenant('cart-unit-8', 'cart-unit-8.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        Cart::create(['customer_id' => $customer->id]);

        expect($customer->cart)->not->toBeNull()
            ->and($customer->cart->customer_id)->toBe($customer->id);
    });
});

test('cart has many items', function () {
    $tenant = $this->createTenant('cart-unit-9', 'cart-unit-9.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        CartItem::create(['cart_id' => $cart->id, 'product_id' => $productA->id, 'quantity' => 1, 'price' => 10]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $productB->id, 'quantity' => 2, 'price' => 20]);

        expect($cart->items)->toHaveCount(2);
    });
});

test('cart item belongs to product', function () {
    $tenant = $this->createTenant('cart-unit-10', 'cart-unit-10.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        expect($item->product->id)->toBe($product->id)
            ->and($item->product->name)->toBe($product->name);
    });
});

test('deleting a cart cascades to cart items', function () {
    $tenant = $this->createTenant('cart-unit-11', 'cart-unit-11.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10.00,
        ]);

        expect(CartItem::count())->toBe(1);

        $cart->delete();

        expect(CartItem::count())->toBe(0);
    });
});

test('deleting a customer cascades to their cart and items', function () {
    $tenant = $this->createTenant('cart-unit-12', 'cart-unit-12.test');

    $this->asTenant($tenant, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 15.00,
        ]);

        expect(Cart::count())->toBe(1)
            ->and(CartItem::count())->toBe(1);

        $customer->delete();

        expect(Cart::count())->toBe(0)
            ->and(CartItem::count())->toBe(0);
    });
});
