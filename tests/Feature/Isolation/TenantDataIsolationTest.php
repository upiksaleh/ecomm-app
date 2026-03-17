<?php

use App\Models\Tenant;
use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use Illuminate\Support\Facades\DB;
use Tests\Traits\CreatesTestTenants;

uses(CreatesTestTenants::class);

// ---------------------------------------------------------------------------
// Product isolation
// ---------------------------------------------------------------------------

test('products created in tenant A are not visible in tenant B', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantA, function () {
        Product::factory()->create(['name' => 'Product Alpha']);
        Product::factory()->create(['name' => 'Product Alpha 2']);
    });

    $this->asTenant($tenantB, function () {
        expect(Product::count())->toBe(0);
    });
});

test('products created in tenant B are not visible in tenant A', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantB, function () {
        Product::factory()->count(3)->create();
    });

    $this->asTenant($tenantA, function () {
        expect(Product::count())->toBe(0);
    });
});

test('each tenant only sees its own products', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantA, function () {
        Product::factory()->count(2)->create();
    });

    $this->asTenant($tenantB, function () {
        Product::factory()->count(5)->create();
    });

    $this->asTenant($tenantA, fn () => expect(Product::count())->toBe(2));
    $this->asTenant($tenantB, fn () => expect(Product::count())->toBe(5));
});

test('product IDs do not leak across tenants', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $productA = $this->asTenant($tenantA, fn () => Product::factory()->create(['name' => 'Alpha Product']));

    $this->asTenant($tenantB, function () use ($productA) {
        expect(Product::find($productA->id))->toBeNull();
    });
});

// ---------------------------------------------------------------------------
// Customer isolation
// ---------------------------------------------------------------------------

test('customers created in tenant A are not visible in tenant B', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantA, function () {
        Customer::factory()->count(3)->create();
    });

    $this->asTenant($tenantB, function () {
        expect(Customer::count())->toBe(0);
    });
});

test('same email address can be registered in two different tenants', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $sharedEmail = 'shared@example.com';

    $this->asTenant($tenantA, function () use ($sharedEmail) {
        Customer::factory()->create(['email' => $sharedEmail]);
    });

    // Should not throw a unique constraint violation in Tenant B.
    $this->asTenant($tenantB, function () use ($sharedEmail) {
        Customer::factory()->create(['email' => $sharedEmail]);
        expect(Customer::where('email', $sharedEmail)->count())->toBe(1);
    });
});

test('customer record from tenant A is not retrievable in tenant B by ID', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $customer = $this->asTenant($tenantA, fn () => Customer::factory()->create());

    $this->asTenant($tenantB, function () use ($customer) {
        expect(Customer::find($customer->id))->toBeNull();
    });
});

// ---------------------------------------------------------------------------
// Cart isolation
// ---------------------------------------------------------------------------

test('carts created in tenant A are not visible in tenant B', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantA, function () {
        $customer = Customer::factory()->create();
        Cart::create(['customer_id' => $customer->id]);
    });

    $this->asTenant($tenantB, function () {
        expect(Cart::count())->toBe(0);
    });
});

test('cart items created in tenant A are not visible in tenant B', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantA, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);
    });

    $this->asTenant($tenantB, function () {
        expect(CartItem::count())->toBe(0);
    });
});

test('cart item from tenant A cannot be accessed by ID in tenant B', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $cartItem = $this->asTenant($tenantA, function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $cart = Cart::create(['customer_id' => $customer->id]);

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 9.99,
        ]);
    });

    $this->asTenant($tenantB, function () use ($cartItem) {
        expect(CartItem::find($cartItem->id))->toBeNull();
    });
});

// ---------------------------------------------------------------------------
// HTTP-level isolation (domain routing)
// ---------------------------------------------------------------------------

test('shop http request to tenant A only returns tenant A products', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantA, fn () => Product::factory()->create(['name' => 'Alpha Widget']));
    $this->asTenant($tenantB, fn () => Product::factory()->create(['name' => 'Beta Gadget']));

    $response = $this->getAsTenant($tenantA, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('tenant/shop/Index')
            ->where('products.data.0.name', 'Alpha Widget')
            ->where('products.total', 1)
    );
});

test('shop http request to tenant B only returns tenant B products', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantA, fn () => Product::factory()->count(3)->create());
    $this->asTenant($tenantB, fn () => Product::factory()->create(['name' => 'Beta Only']));

    $response = $this->getAsTenant($tenantB, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('products.total', 1)
            ->where('products.data.0.name', 'Beta Only')
    );
});

test('inactive products from tenant A are not shown in shop', function () {
    [$tenantA] = $this->createTwoTenants();

    $this->asTenant($tenantA, function () {
        Product::factory()->create(['name' => 'Visible', 'active' => true]);
        Product::factory()->inactive()->create(['name' => 'Hidden']);
    });

    $response = $this->getAsTenant($tenantA, '/shop');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('products.total', 1)
            ->where('products.data.0.name', 'Visible')
    );
});

test('tenant A customer cart is not shared with tenant B customer', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $customerA = $this->asTenant($tenantA, fn () => Customer::factory()->create());
    $productA = $this->asTenant($tenantA, fn () => Product::factory()->create());

    // Customer A adds to cart.
    $this->actingAs($customerA, 'customer')
        ->post($this->tenantUrl($tenantA, '/cart'), ['product_id' => $productA->id, 'quantity' => 2]);

    // Tenant B database should have zero cart items.
    $this->asTenant($tenantB, function () {
        expect(CartItem::count())->toBe(0);
        expect(Cart::count())->toBe(0);
    });
});

test('deleting a tenant removes its database', function () {
    $tenant = $this->createTenant('to-delete', 'to-delete.test');
    $tenantId = $tenant->id;

    // Seed something in the tenant DB.
    $this->asTenant($tenant, fn () => Product::factory()->count(2)->create());

    $prefix = config('tenancy.database.prefix', 'tenant_');
    $suffix = config('tenancy.database.suffix', '_db');
    $dbName = $prefix.$tenantId.$suffix;

    // Confirm the DB exists.
    $exists = DB::select(
        'SELECT 1 FROM pg_database WHERE datname = ?',
        [$dbName]
    );
    expect($exists)->not->toBeEmpty();

    // Delete the tenant — this triggers DeleteDatabase synchronously.
    $tenant->delete();

    // Remove from cleanup list since we already deleted it.
    $this->createdTenantIds = array_filter(
        $this->createdTenantIds,
        fn ($id) => $id !== $tenantId,
    );

    $exists = DB::select(
        'SELECT 1 FROM pg_database WHERE datname = ?',
        [$dbName]
    );
    expect($exists)->toBeEmpty();
});

test('two tenants with the same product SKU remain independent', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $this->asTenant($tenantA, fn () => Product::factory()->create(['sku' => 'SHARED-SKU', 'price' => 10.00]));
    $this->asTenant($tenantB, fn () => Product::factory()->create(['sku' => 'SHARED-SKU', 'price' => 99.99]));

    $priceA = $this->asTenant($tenantA, fn () => Product::where('sku', 'SHARED-SKU')->value('price'));
    $priceB = $this->asTenant($tenantB, fn () => Product::where('sku', 'SHARED-SKU')->value('price'));

    expect((float) $priceA)->toBe(10.00);
    expect((float) $priceB)->toBe(99.99);
});
