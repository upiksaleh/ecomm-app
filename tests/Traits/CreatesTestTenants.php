<?php

namespace Tests\Traits;

use App\Models\Tenant;
use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\User;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

/**
 * Provides helpers for creating real tenants (with databases) in tests.
 *
 * Lifecycle
 * ---------
 * 1. Each createTenant() call provisions a tenant database synchronously
 *    (TenancyServiceProvider has shouldBeQueued = false).
 * 2. tearDownCreatesTestTenants() is called automatically by Laravel's
 *    TestCase::tearDown() via the setUpTraits() convention.
 * 3. Because RefreshDatabase wraps the central DB in a transaction that is
 *    rolled back, Eloquent records may be gone by tearDown — so we drop the
 *    physical tenant databases directly via SQL rather than relying on the
 *    Tenant model's delete event.
 */
trait CreatesTestTenants
{
    /** @var string[] IDs of tenants created during this test */
    protected array $createdTenantIds = [];

    // -------------------------------------------------------------------------
    // Automatic setup / teardown (called by Laravel's TestCase via trait hooks)
    // -------------------------------------------------------------------------

    protected function setUpCreatesTestTenants(): void
    {
        $this->createdTenantIds = [];
    }

    /**
     * Disable RefreshDatabase's connection-level transaction wrapper.
     *
     * RefreshDatabase::beginDatabaseTransaction() iterates over
     * connectionsToTransact() and wraps each connection in a DB transaction.
     * PostgreSQL raises:
     *   ERROR: CREATE DATABASE cannot run inside a transaction block
     * if that transaction is open when Tenant::create() fires the
     * CreateDatabase job.
     *
     * Returning an empty array prevents any connection from being wrapped,
     * sidestepping the collision without overriding the same method name as
     * RefreshDatabase (which would cause a PHP trait-conflict fatal error).
     *
     * tearDownCreatesTestTenants() resets RefreshDatabaseState::$migrated so
     * the next test re-runs migrate:fresh, giving it a clean central DB.
     *
     * @var string[]
     */
    protected array $connectionsToTransact = [];

    protected function tearDownCreatesTestTenants(): void
    {
        // Always leave central context when the test finishes.
        if (tenancy()->tenant !== null) {
            tenancy()->end();
        }

        foreach ($this->createdTenantIds as $id) {
            $this->dropTenantDatabase($id);
        }

        $this->createdTenantIds = [];

        // Because we skipped the transaction wrapper, the central DB now
        // contains whatever data this test wrote.  Reset the migrated flag
        // so that the next test triggers a fresh migrate:fresh, guaranteeing
        // a clean slate regardless of whether that test uses this trait.
        RefreshDatabaseState::$migrated = false;
    }

    // -------------------------------------------------------------------------
    // Tenant creation helpers
    // -------------------------------------------------------------------------

    /**
     * Create a Tenant with a database + a domain record.
     * The physical database is provisioned synchronously.
     */
    protected function createTenant(string $id, string $domain): Tenant
    {
        $tenant = Tenant::create(['id' => $id]);
        $tenant->domains()->create(['domain' => $domain]);

        // Reload so ->domains relation is populated.
        $tenant->load('domains');

        $this->createdTenantIds[] = $id;

        return $tenant;
    }

    /**
     * Create two isolated tenants suitable for isolation tests.
     *
     * @return array{0: Tenant, 1: Tenant}
     */
    protected function createTwoTenants(): array
    {
        return [
            $this->createTenant('tenant-alpha', 'alpha.test'),
            $this->createTenant('tenant-beta', 'beta.test'),
        ];
    }

    // -------------------------------------------------------------------------
    // Tenancy context helpers
    // -------------------------------------------------------------------------

    /**
     * Run a callback inside a tenant's database context, then return to the
     * central context automatically — even on exceptions.
     */
    protected function asTenant(Tenant $tenant, callable $callback): mixed
    {
        tenancy()->initialize($tenant);

        try {
            return $callback();
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Return the HTTP_HOST server-variable array needed to simulate a request
     * arriving at the given tenant's primary domain.
     *
     * Prefer tenantUrl() + the standard get()/post() helpers over this method.
     * withServerVariables(['HTTP_HOST' => ...]) does NOT work reliably because
     * Symfony's Request::create() overwrites HTTP_HOST from the URL host when
     * a fully-qualified URL (produced by TestCase::prepareUrlForRequest) is
     * used — see tenantUrl() for the correct approach.
     *
     * This method is kept for the rare cases where callers need the raw array
     * (e.g. chaining with actingAs before calling get() with a full URL).
     */
    protected function tenantHost(Tenant $tenant): array
    {
        $domain = $tenant->domains->first()?->domain
            ?? ($tenant->id.'.test');

        return ['HTTP_HOST' => $domain];
    }

    /**
     * Build an absolute URL for the given tenant domain + path.
     *
     * Passing the full URL (e.g. http://alpha.test/customer/login) to
     * Laravel's test-client helpers is critical.  When only a relative path
     * like "/customer/login" is passed, TestCase::prepareUrlForRequest()
     * prepends APP_URL ("http://localhost:8000"), producing
     * "http://localhost:8000/customer/login".  Symfony's Request::create()
     * then *overwrites* any HTTP_HOST server-variable override with the host
     * extracted from that URL ("localhost:8000"), so withServerVariables()
     * has no effect on routing.  By using the full tenant-domain URL we let
     * Symfony set HTTP_HOST = "<tenant-domain>" directly from the URL itself.
     */
    protected function tenantUrl(Tenant $tenant, string $path): string
    {
        $domain = $tenant->domains->first()?->domain ?? ($tenant->id.'.test');

        return 'http://'.$domain.'/'.ltrim($path, '/');
    }

    /**
     * Make an HTTP GET request as if it arrived at the tenant's domain.
     */
    protected function getAsTenant(Tenant $tenant, string $url): TestResponse
    {
        return $this->get($this->tenantUrl($tenant, $url));
    }

    /**
     * Make an HTTP POST request as if it arrived at the tenant's domain.
     */
    protected function postAsTenant(Tenant $tenant, string $url, array $data = []): TestResponse
    {
        return $this->post($this->tenantUrl($tenant, $url), $data);
    }

    // -------------------------------------------------------------------------
    // Tenant data seeders (all run inside tenant context)
    // -------------------------------------------------------------------------

    /**
     * Create a tenant admin User inside the given tenant's database and return it.
     * This is the user that authenticates via the 'tenant' guard.
     */
    protected function createTenantAdmin(Tenant $tenant, array $attrs = []): User
    {
        return $this->asTenant($tenant, function () use ($attrs) {
            return User::create(array_merge([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
            ], $attrs));
        });
    }

    /**
     * Create a Product inside the given tenant's database and return it.
     * Accepts an optional attribute override array.
     */
    protected function createProductForTenant(Tenant $tenant, array $attrs = []): Product
    {
        return $this->asTenant($tenant, function () use ($attrs) {
            return Product::factory()->create($attrs);
        });
    }

    /**
     * Create a Customer inside the given tenant's database and return it.
     */
    protected function createCustomerForTenant(Tenant $tenant, array $attrs = []): Customer
    {
        return $this->asTenant($tenant, function () use ($attrs) {
            return Customer::factory()->create($attrs);
        });
    }

    /**
     * Create a Cart (and optional CartItems) for a customer inside a tenant.
     *
     * @param  array<array{product: Product, quantity: int}>  $items
     */
    protected function createCartForCustomer(
        Tenant $tenant,
        Customer $customer,
        array $items = []
    ): Cart {
        return $this->asTenant($tenant, function () use ($customer, $items) {
            /** @var Cart $cart */
            $cart = Cart::create(['customer_id' => $customer->id]);

            foreach ($items as $entry) {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $entry['product']->id,
                    'quantity' => $entry['quantity'],
                    'price' => $entry['product']->price,
                ]);
            }

            return $cart->load('items.product');
        });
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Drop a tenant's physical database directly via SQL.
     *
     * We bypass Eloquent because RefreshDatabase may have already rolled back
     * the transaction that contained the Tenant row, making Tenant::find()
     * return null — while the physical database still exists.
     */
    private function dropTenantDatabase(string $tenantId): void
    {
        $prefix = config('tenancy.database.prefix', 'tenant_');
        $suffix = config('tenancy.database.suffix', '_db');
        $dbName = $prefix.$tenantId.$suffix;

        try {
            // PostgreSQL: terminate active connections first.
            DB::statement('
                SELECT pg_terminate_backend(pid)
                FROM   pg_stat_activity
                WHERE  datname = ?
                AND    pid <> pg_backend_pid()
            ', [$dbName]);

            DB::statement('DROP DATABASE IF EXISTS "'.$dbName.'"');
        } catch (\Throwable) {
            // Silently ignore — the DB may never have been created (e.g. the
            // test failed before Tenant::create() completed).
        }
    }
}
