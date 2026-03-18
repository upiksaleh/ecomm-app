<?php

use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use Tests\Traits\CreatesTestTenants;

uses(CreatesTestTenants::class);

// ---------------------------------------------------------------------------
// Property 5: Search filter correctness
// Feature: customer-management, Property 5: Search filter correctness
// Validates: Requirements 2.1, 2.5
// ---------------------------------------------------------------------------

test('search scope returns only customers matching query in name or email (case-insensitive)', function () {
    $tenant = $this->createTenant('search-test', 'search-test.test');

    // Run 100 iterations for property-based testing
    for ($i = 0; $i < 100; $i++) {
        // Generate random customers with varied names and emails
        $this->asTenant($tenant, function () {
            // Clear previous iteration data
            Customer::query()->delete();

            // Create customers with specific patterns for testing
            $customers = [
                Customer::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']),
                Customer::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@test.com']),
                Customer::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@example.org']),
                Customer::factory()->create(['name' => 'Alice Williams', 'email' => 'alice@sample.net']),
                Customer::factory()->create(['name' => 'Charlie Brown', 'email' => 'charlie@demo.com']),
            ];

            // Generate random search queries from existing data
            $searchQueries = [
                'john',      // Should match John Smith and Bob Johnson
                'JOHN',      // Case insensitive - should match same as above
                'example',   // Should match emails with example.com
                'doe',       // Should match Jane Doe
                'alice',     // Should match Alice Williams
                '@test',     // Should match jane@test.com
                'brown',     // Should match Charlie Brown
                'smith',     // Should match John Smith
                'nonexistent', // Should match nothing
            ];

            $query = $searchQueries[array_rand($searchQueries)];

            // Execute search
            $results = Customer::search($query)->get();

            // Verify all results contain the query (case-insensitive) in name or email
            foreach ($results as $customer) {
                $nameMatch = stripos($customer->name, $query) !== false;
                $emailMatch = stripos($customer->email, $query) !== false;

                expect($nameMatch || $emailMatch)
                    ->toBeTrue(
                        "Customer '{$customer->name}' ({$customer->email}) does not contain '{$query}' in name or email"
                    );
            }

            // Verify no customers were missed (all matching customers are in results)
            $allCustomers = Customer::all();
            foreach ($allCustomers as $customer) {
                $nameMatch = stripos($customer->name, $query) !== false;
                $emailMatch = stripos($customer->email, $query) !== false;

                if ($nameMatch || $emailMatch) {
                    expect($results->contains('id', $customer->id))
                        ->toBeTrue(
                            "Customer '{$customer->name}' ({$customer->email}) should be in results but is missing"
                        );
                }
            }
        });
    }
})->group('property-test', 'customer-management');

test('search scope with empty query returns all customers', function () {
    $tenant = $this->createTenant('search-empty', 'search-empty.test');

    $this->asTenant($tenant, function () {
        // Create random number of customers
        $count = rand(5, 15);
        Customer::factory()->count($count)->create();

        // Search with null
        $resultsNull = Customer::search(null)->get();
        expect($resultsNull)->toHaveCount($count);

        // Search with empty string
        $resultsEmpty = Customer::search('')->get();
        expect($resultsEmpty)->toHaveCount($count);

        // Search with whitespace
        $resultsWhitespace = Customer::search('   ')->get();
        expect($resultsWhitespace)->toHaveCount($count);
    });
})->group('property-test', 'customer-management');

test('search scope is case-insensitive for all variations', function () {
    $tenant = $this->createTenant('search-case', 'search-case.test');

    $this->asTenant($tenant, function () {
        // Create customer with mixed case
        Customer::factory()->create([
            'name' => 'TestUser',
            'email' => 'TestEmail@Example.COM',
        ]);

        // Test various case combinations
        $queries = ['test', 'TEST', 'Test', 'tEsT', 'example', 'EXAMPLE', 'Example'];

        foreach ($queries as $query) {
            $results = Customer::search($query)->get();
            expect($results)->toHaveCount(1, "Query '{$query}' should match the customer");
        }
    });
})->group('property-test', 'customer-management');

test('search scope with special characters is handled safely', function () {
    $tenant = $this->createTenant('search-special', 'search-special.test');

    $this->asTenant($tenant, function () {
        // Create customers with special characters
        Customer::factory()->create(['name' => "O'Brien", 'email' => 'obrien@test.com']);
        Customer::factory()->create(['name' => 'Smith-Jones', 'email' => 'smith.jones@test.com']);
        Customer::factory()->create(['name' => 'Test (Admin)', 'email' => 'admin@test.com']);

        // Test searches with special characters
        $results1 = Customer::search("O'Brien")->get();
        expect($results1)->toHaveCount(1);

        $results2 = Customer::search('Smith-Jones')->get();
        expect($results2)->toHaveCount(1);

        $results3 = Customer::search('(Admin)')->get();
        expect($results3)->toHaveCount(1);

        // Test SQL wildcard characters are escaped
        Customer::factory()->create(['name' => 'Percent User', 'email' => 'percent@test.com']);
        $results4 = Customer::search('%')->get();
        // Should not match all customers, only those with % in name/email
        expect($results4)->toHaveCount(0);
    });
})->group('property-test', 'customer-management');

// ---------------------------------------------------------------------------
// Property 10: Sort order correctness
// Feature: customer-management, Property 10: Sort order correctness
// Validates: Requirements 5.1
// ---------------------------------------------------------------------------

test('sortBy scope orders customers correctly by column and direction', function () {
    $tenant = $this->createTenant('sort-test', 'sort-test.test');

    // Run 100 iterations for property-based testing
    for ($i = 0; $i < 100; $i++) {
        $this->asTenant($tenant, function () {
            // Clear previous iteration data
            Customer::query()->delete();

            // Create random number of customers with varied data
            $count = rand(5, 20);
            $customers = Customer::factory()->count($count)->create();

            // Test all valid sort columns and directions
            $columns = ['name', 'email', 'created_at'];
            $directions = ['asc', 'desc'];

            // Pick a random column and direction for this iteration
            $column = $columns[array_rand($columns)];
            $direction = $directions[array_rand($directions)];

            // Execute sort
            $results = Customer::sortBy($column, $direction)->get();

            // Verify the results are ordered correctly
            $values = $results->pluck($column)->toArray();

            // Create expected sorted array
            $expected = $values;
            if ($direction === 'asc') {
                sort($expected);
            } else {
                rsort($expected);
            }

            // Compare actual vs expected order
            expect($values)->toBe(
                $expected,
                "Customers should be sorted by {$column} in {$direction} order"
            );

            // Verify all customers are returned (no filtering)
            expect($results)->toHaveCount($count);
        });
    }
})->group('property-test', 'customer-management');

test('sortBy scope handles all valid columns', function () {
    $tenant = $this->createTenant('sort-columns', 'sort-columns.test');

    $this->asTenant($tenant, function () {
        // Create customers with distinct values
        Customer::factory()->create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'created_at' => now()->subDays(3),
        ]);
        Customer::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'created_at' => now()->subDays(2),
        ]);
        Customer::factory()->create([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'created_at' => now()->subDays(1),
        ]);

        // Test sort by name ascending
        $byNameAsc = Customer::sortBy('name', 'asc')->pluck('name')->toArray();
        expect($byNameAsc)->toBe(['Alice', 'Bob', 'Charlie']);

        // Test sort by name descending
        $byNameDesc = Customer::sortBy('name', 'desc')->pluck('name')->toArray();
        expect($byNameDesc)->toBe(['Charlie', 'Bob', 'Alice']);

        // Test sort by email ascending
        $byEmailAsc = Customer::sortBy('email', 'asc')->pluck('email')->toArray();
        expect($byEmailAsc)->toBe(['alice@example.com', 'bob@example.com', 'charlie@example.com']);

        // Test sort by email descending
        $byEmailDesc = Customer::sortBy('email', 'desc')->pluck('email')->toArray();
        expect($byEmailDesc)->toBe(['charlie@example.com', 'bob@example.com', 'alice@example.com']);

        // Test sort by created_at ascending (oldest first)
        $byDateAsc = Customer::sortBy('created_at', 'asc')->pluck('name')->toArray();
        expect($byDateAsc)->toBe(['Alice', 'Bob', 'Charlie']);

        // Test sort by created_at descending (newest first)
        $byDateDesc = Customer::sortBy('created_at', 'desc')->pluck('name')->toArray();
        expect($byDateDesc)->toBe(['Charlie', 'Bob', 'Alice']);
    });
})->group('property-test', 'customer-management');

test('sortBy scope defaults to created_at for invalid column', function () {
    $tenant = $this->createTenant('sort-invalid-column', 'sort-invalid-column.test');

    $this->asTenant($tenant, function () {
        // Create customers with different creation times
        $oldest = Customer::factory()->create(['created_at' => now()->subDays(3)]);
        $middle = Customer::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Customer::factory()->create(['created_at' => now()->subDays(1)]);

        // Test with invalid column name and asc direction
        $resultsAsc = Customer::sortBy('invalid_column', 'asc')->pluck('id')->toArray();
        // Should default to created_at with asc direction (oldest first)
        expect($resultsAsc)->toBe([$oldest->id, $middle->id, $newest->id]);

        // Test with invalid column name and desc direction
        $resultsDesc = Customer::sortBy('invalid_column', 'desc')->pluck('id')->toArray();
        // Should default to created_at with desc direction (newest first)
        expect($resultsDesc)->toBe([$newest->id, $middle->id, $oldest->id]);
    });
})->group('property-test', 'customer-management');

test('sortBy scope defaults to desc for invalid direction', function () {
    $tenant = $this->createTenant('sort-invalid-direction', 'sort-invalid-direction.test');

    $this->asTenant($tenant, function () {
        // Create customers with alphabetical names
        Customer::factory()->create(['name' => 'Alice']);
        Customer::factory()->create(['name' => 'Bob']);
        Customer::factory()->create(['name' => 'Charlie']);

        // Test with invalid direction
        $results = Customer::sortBy('name', 'invalid')->pluck('name')->toArray();

        // Should default to desc
        expect($results)->toBe(['Charlie', 'Bob', 'Alice']);
    });
})->group('property-test', 'customer-management');

test('sortBy scope maintains correct order with duplicate values', function () {
    $tenant = $this->createTenant('sort-duplicates', 'sort-duplicates.test');

    $this->asTenant($tenant, function () {
        // Create customers with duplicate names
        $customer1 = Customer::factory()->create(['name' => 'John', 'email' => 'john1@example.com']);
        $customer2 = Customer::factory()->create(['name' => 'John', 'email' => 'john2@example.com']);
        $customer3 = Customer::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);

        // Sort by name ascending
        $results = Customer::sortBy('name', 'asc')->get();

        // Alice should be first, both Johns should be after
        expect($results->first()->name)->toBe('Alice');
        expect($results->skip(1)->take(2)->pluck('name')->unique()->toArray())->toBe(['John']);

        // Verify all customers are returned
        expect($results)->toHaveCount(3);
    });
})->group('property-test', 'customer-management');

// ---------------------------------------------------------------------------
// Property 9: Cascade deletion of associated data
// Feature: customer-management, Property 9: Cascade deletion of associated data
// Validates: Requirements 4.3
// ---------------------------------------------------------------------------

test('deleting a customer cascades to delete cart and cart items', function () {
    $tenant = $this->createTenant('cascade-delete', 'cascade-delete.test');

    // Run 100 iterations for property-based testing
    for ($i = 0; $i < 100; $i++) {
        $this->asTenant($tenant, function () {
            // Clear previous iteration data
            CartItem::query()->delete();
            Cart::query()->delete();
            Customer::query()->delete();
            Product::query()->delete();

            // Generate random number of customers with carts and items
            $customerCount = rand(1, 5);

            foreach (range(1, $customerCount) as $_) {
                // Create a customer
                $customer = Customer::factory()->create();

                // Randomly decide if this customer has a cart (70% chance)
                if (rand(1, 10) <= 7) {
                    // Create a cart for the customer
                    $cart = Cart::factory()->forCustomer($customer)->create();

                    // Create random number of cart items (1-5)
                    $itemCount = rand(1, 5);

                    // Create products for cart items
                    $products = Product::factory()->count($itemCount)->create();

                    foreach ($products as $product) {
                        CartItem::factory()
                            ->forCart($cart)
                            ->forProduct($product)
                            ->quantity(rand(1, 3))
                            ->create();
                    }
                }
            }

            // Pick a random customer to delete
            $allCustomers = Customer::all();
            if ($allCustomers->isEmpty()) {
                return; // Skip this iteration if no customers
            }

            $customerToDelete = $allCustomers->random();
            $customerId = $customerToDelete->id;

            // Get the cart and cart items before deletion
            $cart = $customerToDelete->cart;
            $cartId = $cart?->id;
            $cartItemIds = $cart?->items->pluck('id')->toArray() ?? [];

            // Delete the customer
            $customerToDelete->delete();

            // Verify customer is deleted
            expect(Customer::find($customerId))->toBeNull(
                "Customer {$customerId} should be deleted"
            );

            // Verify cart is deleted (if it existed)
            if ($cartId) {
                expect(Cart::find($cartId))->toBeNull(
                    "Cart {$cartId} should be cascade deleted when customer {$customerId} is deleted"
                );
            }

            // Verify all cart items are deleted (if they existed)
            foreach ($cartItemIds as $cartItemId) {
                expect(CartItem::find($cartItemId))->toBeNull(
                    "CartItem {$cartItemId} should be cascade deleted when cart {$cartId} is deleted"
                );
            }

            // Verify other customers and their data remain intact
            $remainingCustomers = Customer::all();
            foreach ($remainingCustomers as $customer) {
                // If customer has a cart, verify it still exists
                $customerCart = $customer->cart;
                if ($customerCart) {
                    expect(Cart::find($customerCart->id))->not->toBeNull(
                        "Cart {$customerCart->id} for customer {$customer->id} should still exist"
                    );

                    // Verify cart items still exist
                    foreach ($customerCart->items as $item) {
                        expect(CartItem::find($item->id))->not->toBeNull(
                            "CartItem {$item->id} should still exist"
                        );
                    }
                }
            }
        });
    }
})->group('property-test', 'customer-management');

test('cascade deletion works when customer has no cart', function () {
    $tenant = $this->createTenant('cascade-no-cart', 'cascade-no-cart.test');

    $this->asTenant($tenant, function () {
        // Create a customer without a cart
        $customer = Customer::factory()->create();
        $customerId = $customer->id;

        // Verify customer has no cart
        expect($customer->cart)->toBeNull();

        // Delete the customer
        $customer->delete();

        // Verify customer is deleted
        expect(Customer::find($customerId))->toBeNull();
    });
})->group('property-test', 'customer-management');

test('cascade deletion works when cart has no items', function () {
    $tenant = $this->createTenant('cascade-empty-cart', 'cascade-empty-cart.test');

    $this->asTenant($tenant, function () {
        // Create a customer with an empty cart
        $customer = Customer::factory()->create();
        $cart = Cart::factory()->forCustomer($customer)->create();

        $customerId = $customer->id;
        $cartId = $cart->id;

        // Verify cart has no items
        expect($cart->items)->toHaveCount(0);

        // Delete the customer
        $customer->delete();

        // Verify customer is deleted
        expect(Customer::find($customerId))->toBeNull();

        // Verify cart is deleted
        expect(Cart::find($cartId))->toBeNull();
    });
})->group('property-test', 'customer-management');

test('cascade deletion works with multiple cart items', function () {
    $tenant = $this->createTenant('cascade-multiple-items', 'cascade-multiple-items.test');

    $this->asTenant($tenant, function () {
        // Create a customer with a cart containing multiple items
        $customer = Customer::factory()->create();
        $cart = Cart::factory()->forCustomer($customer)->create();

        // Create 10 cart items
        $products = Product::factory()->count(10)->create();
        $cartItemIds = [];

        foreach ($products as $product) {
            $item = CartItem::factory()
                ->forCart($cart)
                ->forProduct($product)
                ->create();
            $cartItemIds[] = $item->id;
        }

        $customerId = $customer->id;
        $cartId = $cart->id;

        // Verify cart has 10 items
        expect($cart->items()->count())->toBe(10);

        // Delete the customer
        $customer->delete();

        // Verify customer is deleted
        expect(Customer::find($customerId))->toBeNull();

        // Verify cart is deleted
        expect(Cart::find($cartId))->toBeNull();

        // Verify all cart items are deleted
        foreach ($cartItemIds as $cartItemId) {
            expect(CartItem::find($cartItemId))->toBeNull(
                "CartItem {$cartItemId} should be cascade deleted"
            );
        }

        // Verify products still exist (should not be cascade deleted)
        foreach ($products as $product) {
            expect(Product::find($product->id))->not->toBeNull(
                "Product {$product->id} should still exist after cart item deletion"
            );
        }
    });
})->group('property-test', 'customer-management');

// ---------------------------------------------------------------------------
// Unit Tests for CustomerController index method
// Requirements: 1.1, 1.3, 1.4, 2.1, 5.1
// ---------------------------------------------------------------------------

test('index returns paginated customers', function () {
    $tenant = $this->createTenant('index-paginated', 'index-paginated.test');
    $admin = $this->createTenantAdmin($tenant);

    // Create 20 customers to test pagination
    $this->asTenant($tenant, fn () => Customer::factory()->count(20)->create());

    $response = $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->has('customers.data', 15) // First page should have 15 customers
                ->where('customers.per_page', 15)
                ->where('customers.total', 20)
                ->where('customers.current_page', 1)
                ->where('customers.last_page', 2)
        );
});

test('index with search parameter filters correctly', function () {
    $tenant = $this->createTenant('index-search', 'index-search.test');
    $admin = $this->createTenantAdmin($tenant);

    // Create customers with specific names and emails
    $this->asTenant($tenant, function () {
        Customer::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']);
        Customer::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@test.com']);
        Customer::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@example.org']);
        Customer::factory()->create(['name' => 'Alice Williams', 'email' => 'alice@sample.net']);
    });

    // Test search by name
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?search=john'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->has('customers.data', 2) // Should match John Smith and Bob Johnson
                ->where('filters.search', 'john')
        );

    // Test search by email
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?search=example'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->has('customers.data', 2) // Should match john@example.com and bob@example.org
                ->where('filters.search', 'example')
        );

    // Test search with no results
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?search=nonexistent'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->has('customers.data', 0)
                ->where('filters.search', 'nonexistent')
        );
});

test('index with sort parameters orders correctly', function () {
    $tenant = $this->createTenant('index-sort', 'index-sort.test');
    $admin = $this->createTenantAdmin($tenant);

    // Create customers with specific data for sorting
    $this->asTenant($tenant, function () {
        Customer::factory()->create([
            'name' => 'Charlie',
            'email' => 'charlie@test.com',
            'created_at' => now()->subDays(3),
        ]);
        Customer::factory()->create([
            'name' => 'Alice',
            'email' => 'alice@test.com',
            'created_at' => now()->subDays(2),
        ]);
        Customer::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@test.com',
            'created_at' => now()->subDays(1),
        ]);
    });

    // Test sort by name ascending
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?sort=name&direction=asc'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->where('customers.data.0.name', 'Alice')
                ->where('customers.data.1.name', 'Bob')
                ->where('customers.data.2.name', 'Charlie')
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'asc')
        );

    // Test sort by name descending
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?sort=name&direction=desc'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->where('customers.data.0.name', 'Charlie')
                ->where('customers.data.1.name', 'Bob')
                ->where('customers.data.2.name', 'Alice')
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'desc')
        );

    // Test sort by email ascending
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?sort=email&direction=asc'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->where('customers.data.0.email', 'alice@test.com')
                ->where('customers.data.1.email', 'bob@test.com')
                ->where('customers.data.2.email', 'charlie@test.com')
                ->where('filters.sort', 'email')
                ->where('filters.direction', 'asc')
        );

    // Test sort by created_at descending (default)
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?sort=created_at&direction=desc'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->where('customers.data.0.name', 'Bob') // Newest
                ->where('customers.data.1.name', 'Alice')
                ->where('customers.data.2.name', 'Charlie') // Oldest
                ->where('filters.sort', 'created_at')
                ->where('filters.direction', 'desc')
        );
});

test('pagination maintains filters and sort', function () {
    $tenant = $this->createTenant('index-pagination-filters', 'index-pagination-filters.test');
    $admin = $this->createTenantAdmin($tenant);

    // Create 20 customers with names containing 'test'
    $this->asTenant($tenant, function () {
        for ($i = 1; $i <= 20; $i++) {
            Customer::factory()->create([
                'name' => "Test User {$i}",
                'email' => "test{$i}@example.com",
            ]);
        }
    });

    // Request page 2 with search and sort parameters
    $response = $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?search=test&sort=name&direction=asc&page=2'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->where('customers.current_page', 2)
                ->has('customers.data', 5) // Page 2 should have remaining 5 customers
                ->where('filters.search', 'test')
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'asc')
        );

    // Verify the pagination URLs contain the query parameters
    $response->assertInertia(
        fn ($page) => $page
            ->where('customers.next_page_url', null) // No page 3
            ->has('customers.prev_page_url') // Should have previous page URL
    );
});

test('index defaults to sort by created_at desc when no sort specified', function () {
    $tenant = $this->createTenant('index-default-sort', 'index-default-sort.test');
    $admin = $this->createTenantAdmin($tenant);

    // Create customers with different creation times
    $this->asTenant($tenant, function () {
        Customer::factory()->create([
            'name' => 'Oldest',
            'created_at' => now()->subDays(3),
        ]);
        Customer::factory()->create([
            'name' => 'Middle',
            'created_at' => now()->subDays(2),
        ]);
        Customer::factory()->create([
            'name' => 'Newest',
            'created_at' => now()->subDays(1),
        ]);
    });

    // Request without sort parameters
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->where('customers.data.0.name', 'Newest') // Newest first
                ->where('customers.data.1.name', 'Middle')
                ->where('customers.data.2.name', 'Oldest')
                ->where('filters.sort', 'created_at')
                ->where('filters.direction', 'desc')
        );
});

test('index requires authentication', function () {
    $tenant = $this->createTenant('index-auth', 'index-auth.test');

    // Create some customers
    $this->asTenant($tenant, fn () => Customer::factory()->count(3)->create());

    // Request without authentication
    $this->get($this->tenantUrl($tenant, '/customers'))
        ->assertRedirect('/login');
});

test('index returns empty list when no customers exist', function () {
    $tenant = $this->createTenant('index-empty', 'index-empty.test');
    $admin = $this->createTenantAdmin($tenant);

    // Don't create any customers
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->has('customers.data', 0)
                ->where('customers.total', 0)
        );
});

test('index search is case-insensitive', function () {
    $tenant = $this->createTenant('index-search-case', 'index-search-case.test');
    $admin = $this->createTenantAdmin($tenant);

    // Create customer with mixed case
    $this->asTenant($tenant, function () {
        Customer::factory()->create([
            'name' => 'TestUser',
            'email' => 'TestEmail@Example.COM',
        ]);
    });

    // Test lowercase search
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?search=testuser'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->has('customers.data', 1)
        );

    // Test uppercase search
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?search=TESTUSER'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->has('customers.data', 1)
        );

    // Test mixed case search
    $this->actingAs($admin, 'tenant')
        ->get($this->tenantUrl($tenant, '/customers?search=TeStUsEr'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('tenant/customers/Index')
                ->has('customers.data', 1)
        );
});
