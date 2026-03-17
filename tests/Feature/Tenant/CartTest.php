<?php

use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use Tests\Traits\CreatesTestTenants;

uses(CreatesTestTenants::class);

// ---------------------------------------------------------------------------
// Access control — guest redirect
// ---------------------------------------------------------------------------

test('guest accessing cart is redirected to customer login, not admin login', function () {

    $tenant = $this->createTenant('cart-1', 'cart-1.test');
    $this->asTenant($tenant, function () use ($tenant) {
        $response = $this->getAsTenant($tenant, '/cart');

        $response->assertRedirect('/customer/login');
    });
});

test('guest adding to cart is redirected to customer login', function () {
    $tenant = $this->createTenant('cart-2', 'cart-2.test');
    $product = $this->createProductForTenant($tenant);

    $response = $this->postAsTenant($tenant, '/cart', ['product_id' => $product->id]);

    $response->assertRedirect('/customer/login');
});

test('guest updating cart item is redirected to customer login', function () {
    $tenant = $this->createTenant('cart-3', 'cart-3.test');

    $response = $this->patch($this->tenantUrl($tenant, '/cart/1'), ['quantity' => 2]);

    $response->assertRedirect('/customer/login');
});

test('guest removing cart item is redirected to customer login', function () {
    $tenant = $this->createTenant('cart-4', 'cart-4.test');

    $response = $this->delete($this->tenantUrl($tenant, '/cart/1'));

    $response->assertRedirect('/customer/login');
});

// ---------------------------------------------------------------------------
// View cart
// ---------------------------------------------------------------------------

test('authenticated customer can view an empty cart', function () {
    $tenant = $this->createTenant('cart-5', 'cart-5.test');
    $customer = $this->createCustomerForTenant($tenant);

    $response = $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, '/cart'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('tenant/cart/Index')
            ->where('cart.items', [])
            ->where('cart.total', 0)
    );
});

test('cart page shows existing items for authenticated customer', function () {
    $tenant = $this->createTenant('cart-6', 'cart-6.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant, ['name' => 'Test Item', 'price' => 20.00]);

    $this->createCartForCustomer($tenant, $customer, [
        ['product' => $product, 'quantity' => 2],
    ]);

    $response = $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, '/cart'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('tenant/cart/Index')
            ->where('cart.items.0.product.name', 'Test Item')
            ->where('cart.items.0.quantity', 2)
            ->where('cart.items.0.price', '20.00')
            ->where('cart.items.0.subtotal', 40)
            ->where('cart.total', 40)
    );
});

test('cart total reflects all items combined', function () {
    $tenant = $this->createTenant('cart-7', 'cart-7.test');
    $customer = $this->createCustomerForTenant($tenant);
    $productA = $this->createProductForTenant($tenant, ['price' => 10.00]);
    $productB = $this->createProductForTenant($tenant, ['price' => 5.00]);

    $this->createCartForCustomer($tenant, $customer, [
        ['product' => $productA, 'quantity' => 3],
        ['product' => $productB, 'quantity' => 2],
    ]);

    $response = $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, '/cart'));

    $response->assertOk();
    $response->assertInertia(
        // (10 * 3) + (5 * 2) = 30 + 10 = 40
        fn ($page) => $page->where('cart.total', 40)
    );
});

// ---------------------------------------------------------------------------
// Add to cart
// ---------------------------------------------------------------------------

test('authenticated customer can add a product to cart', function () {
    $tenant = $this->createTenant('cart-8', 'cart-8.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant, ['price' => 15.00, 'quantity' => 10]);

    $response = $this->actingAs($customer, 'customer')
        ->post($this->tenantUrl($tenant, '/cart'), ['product_id' => $product->id, 'quantity' => 2]);

    $response->assertRedirect();

    $this->asTenant($tenant, function () use ($customer, $product) {
        $cart = Cart::where('customer_id', $customer->id)->first();
        expect($cart)->not->toBeNull();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        expect($item)->not->toBeNull()
            ->and($item->quantity)->toBe(2)
            ->and((float) $item->price)->toBe(15.00);
    });
});

test('adding a product to cart stores the price snapshot', function () {
    $tenant = $this->createTenant('cart-9', 'cart-9.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant, ['price' => 49.99, 'quantity' => 5]);

    $this->actingAs($customer, 'customer')
        ->post($this->tenantUrl($tenant, '/cart'), ['product_id' => $product->id, 'quantity' => 1]);

    // Change the product price after adding to cart.
    $this->asTenant($tenant, function () use ($product) {
        $product->update(['price' => 999.00]);
    });

    // The cart item should still hold the original price.
    $this->asTenant($tenant, function () use ($customer, $product) {
        $cart = Cart::where('customer_id', $customer->id)->first();
        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->first();

        expect((float) $item->price)->toBe(49.99);
    });
});

test('adding to cart fails when product does not exist', function () {
    $tenant = $this->createTenant('cart-11', 'cart-11.test');
    $customer = $this->createCustomerForTenant($tenant);

    $response = $this->actingAs($customer, 'customer')
        ->post($this->tenantUrl($tenant, '/cart'), ['product_id' => 99999, 'quantity' => 1]);

    $response->assertSessionHasErrors('product_id');
});

test('adding an inactive product to cart is rejected', function () {
    $tenant = $this->createTenant('cart-12', 'cart-12.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant, ['active' => false]);

    $response = $this->actingAs($customer, 'customer')
        ->post($this->tenantUrl($tenant, '/cart'), ['product_id' => $product->id, 'quantity' => 1]);

    $response->assertStatus(422);
});

test('add to cart validates quantity must be at least 1', function () {
    $tenant = $this->createTenant('cart-13', 'cart-13.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant);

    $response = $this->actingAs($customer, 'customer')
        ->post($this->tenantUrl($tenant, '/cart'), ['product_id' => $product->id, 'quantity' => 0]);

    $response->assertSessionHasErrors('quantity');
});

test('add to cart validates quantity cannot exceed 100', function () {
    $tenant = $this->createTenant('cart-14', 'cart-14.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant);

    $response = $this->actingAs($customer, 'customer')
        ->post($this->tenantUrl($tenant, '/cart'), ['product_id' => $product->id, 'quantity' => 101]);

    $response->assertSessionHasErrors('quantity');
});

// ---------------------------------------------------------------------------
// Update cart item quantity
// ---------------------------------------------------------------------------

test('authenticated customer can update a cart item quantity', function () {
    $tenant = $this->createTenant('cart-15', 'cart-15.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant);

    $this->createCartForCustomer($tenant, $customer, [
        ['product' => $product, 'quantity' => 1],
    ]);

    $item = $this->asTenant(
        $tenant,
        fn () => CartItem::whereHas('cart', fn ($q) => $q->where('customer_id', $customer->id))->first()
    );

    $response = $this->actingAs($customer, 'customer')
        ->patch($this->tenantUrl($tenant, "/cart/{$item->id}"), ['quantity' => 5]);

    $response->assertRedirect();

    $this->asTenant($tenant, function () use ($item) {
        expect(CartItem::find($item->id)->quantity)->toBe(5);
    });
});

test('update cart item fails with quantity less than 1', function () {
    $tenant = $this->createTenant('cart-16', 'cart-16.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant);

    $this->createCartForCustomer($tenant, $customer, [
        ['product' => $product, 'quantity' => 2],
    ]);

    $item = $this->asTenant(
        $tenant,
        fn () => CartItem::whereHas('cart', fn ($q) => $q->where('customer_id', $customer->id))->first()
    );

    $this->actingAs($customer, 'customer')
        ->patch($this->tenantUrl($tenant, "/cart/{$item->id}"), ['quantity' => 0])
        ->assertSessionHasErrors('quantity');
});

// ---------------------------------------------------------------------------
// Remove cart item
// ---------------------------------------------------------------------------

test('authenticated customer can remove an item from cart', function () {
    $tenant = $this->createTenant('cart-17', 'cart-17.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant);

    $this->createCartForCustomer($tenant, $customer, [
        ['product' => $product, 'quantity' => 1],
    ]);

    $item = $this->asTenant(
        $tenant,
        fn () => CartItem::whereHas('cart', fn ($q) => $q->where('customer_id', $customer->id))->first()
    );

    $response = $this->actingAs($customer, 'customer')
        ->delete($this->tenantUrl($tenant, "/cart/{$item->id}"));

    $response->assertRedirect();

    $this->asTenant($tenant, function () use ($item) {
        expect(CartItem::find($item->id))->toBeNull();
    });
});

// ---------------------------------------------------------------------------
// Cross-customer access control (ownership enforcement)
// ---------------------------------------------------------------------------

test('customer cannot update another customers cart item', function () {
    $tenant = $this->createTenant('cart-18', 'cart-18.test');
    $ownerA = $this->createCustomerForTenant($tenant);
    $ownerB = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant);

    $this->createCartForCustomer($tenant, $ownerA, [
        ['product' => $product, 'quantity' => 1],
    ]);

    $item = $this->asTenant(
        $tenant,
        fn () => CartItem::whereHas('cart', fn ($q) => $q->where('customer_id', $ownerA->id))->first()
    );

    // Customer B tries to update Customer A's cart item.
    $response = $this->actingAs($ownerB, 'customer')
        ->patch($this->tenantUrl($tenant, "/cart/{$item->id}"), ['quantity' => 99]);

    $response->assertForbidden();

    $this->asTenant($tenant, function () use ($item) {
        expect(CartItem::find($item->id)->quantity)->toBe(1);
    });
});

test('customer cannot remove another customers cart item', function () {
    $tenant = $this->createTenant('cart-19', 'cart-19.test');
    $ownerA = $this->createCustomerForTenant($tenant);
    $ownerB = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant);

    $this->createCartForCustomer($tenant, $ownerA, [
        ['product' => $product, 'quantity' => 1],
    ]);

    $item = $this->asTenant(
        $tenant,
        fn () => CartItem::whereHas('cart', fn ($q) => $q->where('customer_id', $ownerA->id))->first()
    );

    // Customer B tries to delete Customer A's cart item.
    $response = $this->actingAs($ownerB, 'customer')
        ->delete($this->tenantUrl($tenant, "/cart/{$item->id}"));

    $response->assertForbidden();

    $this->asTenant($tenant, function () use ($item) {
        expect(CartItem::find($item->id))->not->toBeNull();
    });
});
