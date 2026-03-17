<?php

use App\Models\Central\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Tests\Traits\CreatesTestTenants;

uses(CreatesTestTenants::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function centralAdmin(): User
{
    return User::create([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
    ]);
}

// ---------------------------------------------------------------------------
// Guest access — all management routes require authentication
// ---------------------------------------------------------------------------

test('guest cannot list tenants', function () {
    $this->get(route('central.tenants.index'))
        ->assertRedirect(route('central.login'));
});

test('guest cannot view create tenant form', function () {
    $this->get(route('central.tenants.create'))
        ->assertRedirect(route('central.login'));
});

test('guest cannot store a new tenant', function () {
    $this->post(route('central.tenants.store'), [
        'id' => 'new-tenant',
        'domain' => 'new-tenant.test',
    ])->assertRedirect(route('central.login'));
});

test('guest cannot view a tenant', function () {
    $tenant = $this->createTenant('guest-show', 'guest-show.test');

    $this->get(route('central.tenants.show', $tenant))
        ->assertRedirect(route('central.login'));
});

test('guest cannot view edit tenant form', function () {
    $tenant = $this->createTenant('guest-edit', 'guest-edit.test');

    $this->get(route('central.tenants.edit', $tenant))
        ->assertRedirect(route('central.login'));
});

test('guest cannot update a tenant', function () {
    $tenant = $this->createTenant('guest-update', 'guest-update.test');

    $this->put(route('central.tenants.update', $tenant), [
        'domain' => 'new-domain.test',
    ])->assertRedirect(route('central.login'));
});

test('guest cannot delete a tenant', function () {
    $tenant = $this->createTenant('guest-delete', 'guest-delete.test');

    $this->delete(route('central.tenants.destroy', $tenant))
        ->assertRedirect(route('central.login'));
});

// ---------------------------------------------------------------------------
// Index
// ---------------------------------------------------------------------------

test('admin can list tenants', function () {
    $admin = centralAdmin();

    $this->createTenant('list-a', 'list-a.test');
    $this->createTenant('list-b', 'list-b.test');

    $this->actingAs($admin, 'central')
        ->get(route('central.tenants.index'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('central/tenants/Index')
                ->where('tenants.total', 2)
        );
});

test('tenant list is paginated', function () {
    $admin = centralAdmin();

    foreach (range(1, 20) as $i) {
        $id = "paginate-{$i}";
        $this->createTenant($id, "{$id}.test");
    }

    $this->actingAs($admin, 'central')
        ->get(route('central.tenants.index'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->where('tenants.per_page', 15)
                ->where('tenants.last_page', 2)
        );
});

// ---------------------------------------------------------------------------
// Create form
// ---------------------------------------------------------------------------

test('admin can view the create tenant form', function () {
    $admin = centralAdmin();

    $this->actingAs($admin, 'central')
        ->get(route('central.tenants.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('central/tenants/Create'));
});

// ---------------------------------------------------------------------------
// Store — success
// ---------------------------------------------------------------------------

test('admin can create a tenant with valid data', function () {
    $admin = centralAdmin();

    $response = $this->actingAs($admin, 'central')
        ->post(route('central.tenants.store'), [
            'id' => 'new-store',
            'domain' => 'new-store.test',
        ]);

    $response->assertRedirect(route('central.tenants.index'));
    $response->assertSessionHas('success');

    expect(Tenant::find('new-store'))->not->toBeNull();

    $this->createdTenantIds[] = 'new-store';
});

test('creating a tenant provisions its database', function () {
    $admin = centralAdmin();

    $this->actingAs($admin, 'central')
        ->post(route('central.tenants.store'), [
            'id' => 'db-provision',
            'domain' => 'db-provision.test',
        ]);

    $this->createdTenantIds[] = 'db-provision';

    $prefix = config('tenancy.database.prefix', 'tenant_');
    $suffix = config('tenancy.database.suffix', '_db');
    $dbName = $prefix.'db-provision'.$suffix;

    $exists = DB::select('SELECT 1 FROM pg_database WHERE datname = ?', [$dbName]);

    expect($exists)->not->toBeEmpty();
});

test('creating a tenant also creates a domain record', function () {
    $admin = centralAdmin();

    $this->actingAs($admin, 'central')
        ->post(route('central.tenants.store'), [
            'id' => 'domain-check',
            'domain' => 'domain-check.test',
        ]);

    $this->createdTenantIds[] = 'domain-check';

    $tenant = Tenant::with('domains')->find('domain-check');

    expect($tenant->domains)->toHaveCount(1)
        ->and($tenant->domains->first()->domain)->toBe('domain-check.test');
});

// ---------------------------------------------------------------------------
// Store — validation
// ---------------------------------------------------------------------------

test('creating a tenant requires an id', function () {
    $admin = centralAdmin();

    $this->actingAs($admin, 'central')
        ->post(route('central.tenants.store'), [
            'domain' => 'no-id.test',
        ])
        ->assertSessionHasErrors('id');
});

test('creating a tenant requires a domain', function () {
    $admin = centralAdmin();

    $this->actingAs($admin, 'central')
        ->post(route('central.tenants.store'), [
            'id' => 'no-domain',
        ])
        ->assertSessionHasErrors('domain');
});

test('tenant id must be unique', function () {
    $admin = centralAdmin();

    $this->createTenant('duplicate-id', 'first.test');

    $this->actingAs($admin, 'central')
        ->post(route('central.tenants.store'), [
            'id' => 'duplicate-id',
            'domain' => 'second.test',
        ])
        ->assertSessionHasErrors('id');
});

test('tenant domain must be unique', function () {
    $admin = centralAdmin();

    $this->createTenant('first-tenant', 'same-domain.test');

    $this->actingAs($admin, 'central')
        ->post(route('central.tenants.store'), [
            'id' => 'second-tenant',
            'domain' => 'same-domain.test',
        ])
        ->assertSessionHasErrors('domain');

    $this->createdTenantIds[] = 'second-tenant';
});

test('tenant id only allows lowercase letters numbers hyphens and underscores', function () {
    $admin = centralAdmin();

    $invalidIds = ['Has Space', 'UPPERCASE', 'special!char', 'dot.here'];

    foreach ($invalidIds as $invalidId) {
        $this->actingAs($admin, 'central')
            ->post(route('central.tenants.store'), [
                'id' => $invalidId,
                'domain' => 'valid-domain.test',
            ])
            ->assertSessionHasErrors('id');
    }
});

test('tenant id with valid characters is accepted', function () {
    $admin = centralAdmin();

    $response = $this->actingAs($admin, 'central')
        ->post(route('central.tenants.store'), [
            'id' => 'valid-id_123',
            'domain' => 'valid-chars.test',
        ]);

    $response->assertRedirect(route('central.tenants.index'));
    $this->createdTenantIds[] = 'valid-id_123';
});

// ---------------------------------------------------------------------------
// Show
// ---------------------------------------------------------------------------

test('admin can view a tenant detail page', function () {
    $admin = centralAdmin();
    $tenant = $this->createTenant('show-me', 'show-me.test');

    $this->actingAs($admin, 'central')
        ->get(route('central.tenants.show', $tenant))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('central/tenants/Show')
                ->where('tenant.id', 'show-me')
        );
});

test('show page includes the tenant domain', function () {
    $admin = centralAdmin();
    $tenant = $this->createTenant('show-domain', 'show-domain.test');

    $this->actingAs($admin, 'central')
        ->get(route('central.tenants.show', $tenant))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->where('tenant.domains.0.domain', 'show-domain.test')
        );
});

// ---------------------------------------------------------------------------
// Edit form
// ---------------------------------------------------------------------------

test('admin can view the edit tenant form', function () {
    $admin = centralAdmin();
    $tenant = $this->createTenant('edit-form', 'edit-form.test');

    $this->actingAs($admin, 'central')
        ->get(route('central.tenants.edit', $tenant))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('central/tenants/Edit')
                ->where('tenant.id', 'edit-form')
        );
});

// ---------------------------------------------------------------------------
// Update
// ---------------------------------------------------------------------------

test('admin can update a tenant domain', function () {
    $admin = centralAdmin();
    $tenant = $this->createTenant('to-update', 'old-domain.test');

    $this->actingAs($admin, 'central')
        ->put(route('central.tenants.update', $tenant), [
            'domain' => 'new-domain.test',
        ])
        ->assertRedirect(route('central.tenants.index'))
        ->assertSessionHas('success');

    $tenant->refresh()->load('domains');

    expect($tenant->domains->first()->domain)->toBe('new-domain.test');
});

test('updating a tenant to the same domain succeeds', function () {
    $admin = centralAdmin();
    $tenant = $this->createTenant('same-domain', 'keep-domain.test');

    $this->actingAs($admin, 'central')
        ->put(route('central.tenants.update', $tenant), [
            'domain' => 'keep-domain.test',
        ])
        ->assertRedirect(route('central.tenants.index'));
});

test('updating a tenant to a domain used by another tenant fails', function () {
    $admin = centralAdmin();

    $this->createTenant('taken-owner', 'taken.test');
    $tenant = $this->createTenant('to-update-fail', 'mine.test');

    $this->actingAs($admin, 'central')
        ->put(route('central.tenants.update', $tenant), [
            'domain' => 'taken.test',
        ])
        ->assertSessionHasErrors('domain');
});

test('updating a tenant requires a domain', function () {
    $admin = centralAdmin();
    $tenant = $this->createTenant('update-no-domain', 'update-no-domain.test');

    $this->actingAs($admin, 'central')
        ->put(route('central.tenants.update', $tenant), [])
        ->assertSessionHasErrors('domain');
});

// ---------------------------------------------------------------------------
// Destroy
// ---------------------------------------------------------------------------

test('admin can delete a tenant', function () {
    $admin = centralAdmin();
    $tenant = $this->createTenant('to-destroy', 'to-destroy.test');

    $this->actingAs($admin, 'central')
        ->delete(route('central.tenants.destroy', $tenant))
        ->assertRedirect(route('central.tenants.index'))
        ->assertSessionHas('success');

    expect(Tenant::find('to-destroy'))->toBeNull();

    // Remove from cleanup list since Eloquent delete already ran.
    $this->createdTenantIds = array_filter(
        $this->createdTenantIds,
        fn ($id) => $id !== 'to-destroy',
    );
});

test('deleting a tenant drops its database', function () {
    $admin = centralAdmin();
    $tenant = $this->createTenant('destroy-db', 'destroy-db.test');

    $prefix = config('tenancy.database.prefix', 'tenant_');
    $suffix = config('tenancy.database.suffix', '_db');
    $dbName = $prefix.'destroy-db'.$suffix;

    $this->actingAs($admin, 'central')
        ->delete(route('central.tenants.destroy', $tenant));

    $this->createdTenantIds = array_filter(
        $this->createdTenantIds,
        fn ($id) => $id !== 'destroy-db',
    );

    $exists = DB::select('SELECT 1 FROM pg_database WHERE datname = ?', [$dbName]);

    expect($exists)->toBeEmpty();
});

test('deleting a non-existent tenant returns 404', function () {
    $admin = centralAdmin();

    $this->actingAs($admin, 'central')
        ->delete(route('central.tenants.destroy', 'ghost-tenant'))
        ->assertNotFound();
});
