<?php

use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use Tests\Traits\CreatesTestTenants;

uses(CreatesTestTenants::class);

// ---------------------------------------------------------------------------
// Guest shop browsing
// ---------------------------------------------------------------------------

test('guest can view the shop index page', function () {
    $tenant = $this->createTenant('shop-1', 'shop-1.test');

    $response = $this->getAsTenant($tenant, '/shop');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('tenant/shop/Index'));
});

test('shop index shows active products', function () {
    $tenant = $this->createTenant('shop-2', 'shop-2.test');

    $this->asTenant($tenant, function () {
        Product::factory()->create(['name' => 'Visible Product', 'active' => true]);
    });

    $response = $this->getAsTenant($tenant, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('tenant/shop/Index')
            ->where('products.total', 1)
            ->where('products.data.0.name', 'Visible Product')
    );
});

test('shop index does not show inactive products', function () {
    $tenant = $this->createTenant('shop-3', 'shop-3.test');

    $this->asTenant($tenant, function () {
        Product::factory()->create(['name' => 'Active',   'active' => true]);
        Product::factory()->inactive()->create(['name' => 'Inactive']);
    });

    $response = $this->getAsTenant($tenant, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('products.total', 1)
            ->where('products.data.0.name', 'Active')
    );
});

test('shop index is empty when there are no active products', function () {
    $tenant = $this->createTenant('shop-4', 'shop-4.test');

    $this->asTenant($tenant, function () {
        Product::factory()->inactive()->count(3)->create();
    });

    $response = $this->getAsTenant($tenant, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('products.total', 0)
    );
});

test('shop index paginates products', function () {
    $tenant = $this->createTenant('shop-5', 'shop-5.test');

    $this->asTenant($tenant, function () {
        Product::factory()->count(15)->create(['active' => true]);
    });

    $response = $this->getAsTenant($tenant, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('products.last_page', 2)
            ->where('products.per_page', 12)
    );
});

// ---------------------------------------------------------------------------
// Product detail
// ---------------------------------------------------------------------------

test('guest can view an active product detail page', function () {
    $tenant = $this->createTenant('shop-6', 'shop-6.test');
    $product = $this->createProductForTenant($tenant, ['name' => 'Detail Product', 'active' => true]);

    $response = $this->getAsTenant($tenant, "/shop/{$product->id}");

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('tenant/shop/Show')
            ->where('product.id', $product->id)
            ->where('product.name', 'Detail Product')
    );
});

test('inactive product detail page returns 404', function () {
    $tenant = $this->createTenant('shop-7', 'shop-7.test');
    $product = $this->createProductForTenant($tenant, ['active' => false]);

    $response = $this->getAsTenant($tenant, "/shop/{$product->id}");

    $response->assertNotFound();
});

test('non-existent product detail page returns 404', function () {
    $tenant = $this->createTenant('shop-8', 'shop-8.test');

    $response = $this->getAsTenant($tenant, '/shop/99999');

    $response->assertNotFound();
});

// ---------------------------------------------------------------------------
// Authenticated customer sees add-to-cart affordance
// ---------------------------------------------------------------------------

test('authenticated customer can view shop and product pages', function () {
    $tenant = $this->createTenant('shop-9', 'shop-9.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant, ['active' => true]);

    $shopResponse = $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, '/shop'));

    $shopResponse->assertOk();

    $detailResponse = $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, "/shop/{$product->id}"));

    $detailResponse->assertOk();
});

test('inertia page props include auth customer when logged in', function () {
    $tenant = $this->createTenant('shop-10', 'shop-10.test');
    $customer = $this->createCustomerForTenant($tenant);

    $response = $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, '/shop'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('auth.customer.id', $customer->id)
    );
});

test('inertia page props have null auth customer for guests', function () {
    $tenant = $this->createTenant('shop-11', 'shop-11.test');

    $response = $this->getAsTenant($tenant, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('auth.customer', null)
    );
});

// ---------------------------------------------------------------------------
// Cart count in shared props
// ---------------------------------------------------------------------------

test('cart count is zero for a guest', function () {
    $tenant = $this->createTenant('shop-12', 'shop-12.test');

    $response = $this->getAsTenant($tenant, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('cart_count', 0)
    );
});

test('cart count reflects items in authenticated customer cart', function () {
    $tenant = $this->createTenant('shop-13', 'shop-13.test');
    $customer = $this->createCustomerForTenant($tenant);
    $product = $this->createProductForTenant($tenant);

    // Add 3 units of the product to the cart.
    $this->createCartForCustomer($tenant, $customer, [
        ['product' => $product, 'quantity' => 3],
    ]);

    $response = $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, '/shop'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('cart_count', 3)
    );
});
