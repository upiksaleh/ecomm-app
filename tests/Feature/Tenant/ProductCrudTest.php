<?php

use App\Models\Tenant\Product;
use Tests\Traits\CreatesTestTenants;

uses(CreatesTestTenants::class);

// ---------------------------------------------------------------------------
// Guest access — all product management routes require tenant admin auth
// ---------------------------------------------------------------------------

test('guest cannot view product index', function () {
    $tenant = $this->createTenant('prod-1', 'prod-1.test');

    $this->getAsTenant($tenant, '/products')
        ->assertRedirect('/login');
});

test('guest cannot view product create form', function () {
    $tenant = $this->createTenant('prod-2', 'prod-2.test');

    $this->getAsTenant($tenant, '/products/create')
        ->assertRedirect('/login');
});

test('guest cannot store a product', function () {
    $tenant = $this->createTenant('prod-3', 'prod-3.test');

    $this->postAsTenant($tenant, '/products', [
        'name' => 'Ghost Product',
        'sku' => 'GHOST-01',
        'price' => 10.00,
        'quantity' => 5,
    ])->assertRedirect('/login');
});

test('guest cannot view product detail', function () {
    $tenant = $this->createTenant('prod-4', 'prod-4.test');
    $product = $this->createProductForTenant($tenant);

    $this->getAsTenant($tenant, "/products/{$product->id}")
        ->assertRedirect('/login');
});

test('guest cannot view product edit form', function () {
    $tenant = $this->createTenant('prod-5', 'prod-5.test');
    $product = $this->createProductForTenant($tenant);

    $this->getAsTenant($tenant, "/products/{$product->id}/edit")
        ->assertRedirect('/login');
});

test('guest cannot update a product', function () {
    $tenant = $this->createTenant('prod-6', 'prod-6.test');
    $product = $this->createProductForTenant($tenant);

    $this->put($this->tenantUrl($tenant, "/products/{$product->id}"), [
        'name' => 'Updated',
        'sku' => 'UPDATED-01',
        'price' => 20.00,
        'quantity' => 10,
    ])->assertRedirect('/login');
});

test('guest cannot delete a product', function () {
    $tenant = $this->createTenant('prod-7', 'prod-7.test');
    $product = $this->createProductForTenant($tenant);

    $this->delete($this->tenantUrl($tenant, "/products/{$product->id}"))
        ->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Authenticated tenant admin — read
// ---------------------------------------------------------------------------

test('admin can view product index', function () {
    $tenant = $this->createTenant('prod-8', 'prod-8.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->asTenant($tenant, fn () => Product::factory()->count(3)->create());

    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/products'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/products/Index')
                ->where('products.total', 3)
        );
});

test('admin can view the create product form', function () {
    $tenant = $this->createTenant('prod-9', 'prod-9.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/products/create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tenant/products/Create'));
});

test('admin can view a product detail page', function () {
    $tenant = $this->createTenant('prod-10', 'prod-10.test');
    $admin = $this->createTenantAdmin($tenant);
    $product = $this->createProductForTenant($tenant, ['name' => 'Detail View']);

    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, "/products/{$product->id}"))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/products/Show')
                ->where('product.name', 'Detail View')
        );
});

test('admin can view the product edit form', function () {
    $tenant = $this->createTenant('prod-11', 'prod-11.test');
    $admin = $this->createTenantAdmin($tenant);
    $product = $this->createProductForTenant($tenant, ['name' => 'Edit Me']);

    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, "/products/{$product->id}/edit"))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/products/Edit')
                ->where('product.name', 'Edit Me')
        );
});

// ---------------------------------------------------------------------------
// Store — success
// ---------------------------------------------------------------------------

test('admin can create a product with valid data', function () {
    $tenant = $this->createTenant('prod-12', 'prod-12.test');
    $admin = $this->createTenantAdmin($tenant);

    $response = $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'name' => 'New Widget',
            'sku' => 'WIDGET-001',
            'description' => 'A great widget',
            'price' => 29.99,
            'quantity' => 50,
            'active' => true,
        ]);

    $response->assertRedirect('/products')
        ->assertSessionHas('success');

    $this->asTenant($tenant, function () {
        expect(Product::where('sku', 'WIDGET-001')->exists())->toBeTrue();
    });
});

test('newly created product is active by default when checked', function () {
    $tenant = $this->createTenant('prod-13', 'prod-13.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'name' => 'Active Product',
            'sku' => 'ACTIVE-001',
            'price' => 10.00,
            'quantity' => 5,
            'active' => true,
        ]);

    $this->asTenant($tenant, function () {
        $product = Product::where('sku', 'ACTIVE-001')->first();
        expect($product->active)->toBeTrue();
    });
});

// ---------------------------------------------------------------------------
// Store — validation
// ---------------------------------------------------------------------------

test('store requires product name', function () {
    $tenant = $this->createTenant('prod-14', 'prod-14.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'sku' => 'NO-NAME',
            'price' => 10.00,
            'quantity' => 5,
        ])
        ->assertSessionHasErrors('name');
});

test('store requires product sku', function () {
    $tenant = $this->createTenant('prod-15', 'prod-15.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'name' => 'No SKU',
            'price' => 10.00,
            'quantity' => 5,
        ])
        ->assertSessionHasErrors('sku');
});

test('store requires a unique sku', function () {
    $tenant = $this->createTenant('prod-16', 'prod-16.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->asTenant($tenant, fn () => Product::factory()->create(['sku' => 'DUPE-SKU']));

    $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'name' => 'Duplicate SKU',
            'sku' => 'DUPE-SKU',
            'price' => 10.00,
            'quantity' => 5,
        ])
        ->assertSessionHasErrors('sku');
});

test('store requires price', function () {
    $tenant = $this->createTenant('prod-17', 'prod-17.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'name' => 'No Price',
            'sku' => 'NO-PRICE',
            'quantity' => 5,
        ])
        ->assertSessionHasErrors('price');
});

test('store requires price to be non-negative', function () {
    $tenant = $this->createTenant('prod-18', 'prod-18.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'name' => 'Negative Price',
            'sku' => 'NEG-PRICE',
            'price' => -1,
            'quantity' => 5,
        ])
        ->assertSessionHasErrors('price');
});

test('store requires quantity', function () {
    $tenant = $this->createTenant('prod-19', 'prod-19.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'name' => 'No Qty',
            'sku' => 'NO-QTY',
            'price' => 10.00,
        ])
        ->assertSessionHasErrors('quantity');
});

test('store requires quantity to be non-negative', function () {
    $tenant = $this->createTenant('prod-20', 'prod-20.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->post($this->tenantUrl($tenant, '/products'), [
            'name' => 'Neg Qty',
            'sku' => 'NEG-QTY',
            'price' => 10.00,
            'quantity' => -1,
        ])
        ->assertSessionHasErrors('quantity');
});

// ---------------------------------------------------------------------------
// Update
// ---------------------------------------------------------------------------

test('admin can update a product', function () {
    $tenant = $this->createTenant('prod-21', 'prod-21.test');
    $admin = $this->createTenantAdmin($tenant);
    $product = $this->createProductForTenant($tenant, ['name' => 'Old Name', 'sku' => 'OLD-SKU']);

    $this->actingAs($admin, 'tenant')
        ->put($this->tenantUrl($tenant, "/products/{$product->id}"), [
            'name' => 'New Name',
            'sku' => 'NEW-SKU',
            'description' => 'Updated description',
            'price' => 99.99,
            'quantity' => 10,
            'active' => true,
        ])
        ->assertRedirect('/products')
        ->assertSessionHas('success');

    $this->asTenant($tenant, function () use ($product) {
        $updated = Product::find($product->id);
        expect($updated->name)->toBe('New Name')
            ->and($updated->sku)->toBe('NEW-SKU')
            ->and((float) $updated->price)->toBe(99.99);
    });
});

test('update allows keeping the same sku on the same product', function () {
    $tenant = $this->createTenant('prod-22', 'prod-22.test');
    $admin = $this->createTenantAdmin($tenant);
    $product = $this->createProductForTenant($tenant, ['sku' => 'KEEP-SKU']);

    $this->actingAs($admin, 'tenant')
        ->put($this->tenantUrl($tenant, "/products/{$product->id}"), [
            'name' => 'Same SKU Product',
            'sku' => 'KEEP-SKU',
            'price' => 20.00,
            'quantity' => 10,
        ])
        ->assertRedirect('/products');
});

test('update rejects a sku already used by another product', function () {
    $tenant = $this->createTenant('prod-23', 'prod-23.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->asTenant($tenant, fn () => Product::factory()->create(['sku' => 'OTHER-SKU']));
    $product = $this->createProductForTenant($tenant, ['sku' => 'MY-SKU']);

    $this->actingAs($admin, 'tenant')
        ->put($this->tenantUrl($tenant, "/products/{$product->id}"), [
            'name' => 'Conflict',
            'sku' => 'OTHER-SKU',
            'price' => 10.00,
            'quantity' => 1,
        ])
        ->assertSessionHasErrors('sku');
});

// ---------------------------------------------------------------------------
// Destroy
// ---------------------------------------------------------------------------

test('admin can delete a product', function () {
    $tenant = $this->createTenant('prod-24', 'prod-24.test');
    $admin = $this->createTenantAdmin($tenant);
    $product = $this->createProductForTenant($tenant);

    $this->actingAs($admin, 'tenant')
        ->delete($this->tenantUrl($tenant, "/products/{$product->id}"))
        ->assertRedirect('/products')
        ->assertSessionHas('success');

    $this->asTenant($tenant, function () use ($product) {
        expect(Product::find($product->id))->toBeNull();
    });
});

test('deleting a non-existent product returns 404', function () {
    $tenant = $this->createTenant('prod-25', 'prod-25.test');
    $admin = $this->createTenantAdmin($tenant);

    $this->actingAs($admin, 'tenant')
        ->delete($this->tenantUrl($tenant, '/products/99999'))
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// SKU uniqueness is per-tenant, not global
// ---------------------------------------------------------------------------

test('two tenants can have products with the same sku independently', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $adminA = $this->createTenantAdmin($tenantA);
    $adminB = $this->createTenantAdmin($tenantB);

    $this->actingAs($adminA, 'tenant')
        ->post($this->tenantUrl($tenantA, '/products'), [
            'name' => 'Product in A',
            'sku' => 'SHARED-SKU',
            'price' => 10.00,
            'quantity' => 5,
        ])
        ->assertRedirect('/products');

    $this->actingAs($adminB, 'tenant')
        ->post($this->tenantUrl($tenantB, '/products'), [
            'name' => 'Product in B',
            'sku' => 'SHARED-SKU',
            'price' => 99.99,
            'quantity' => 3,
        ])
        ->assertRedirect('/products');

    $countA = $this->asTenant($tenantA, fn () => Product::where('sku', 'SHARED-SKU')->count());
    $countB = $this->asTenant($tenantB, fn () => Product::where('sku', 'SHARED-SKU')->count());

    expect($countA)->toBe(1)
        ->and($countB)->toBe(1);
});
