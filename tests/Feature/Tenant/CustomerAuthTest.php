<?php

use App\Models\Tenant\Customer;
use App\Models\Tenant\User;
use Tests\Traits\CreatesTestTenants;

uses(CreatesTestTenants::class);

// ---------------------------------------------------------------------------
// Login page
// ---------------------------------------------------------------------------

test('customer can view the login page', function () {
    $tenant = $this->createTenant('cauth-1', 'cauth-1.test');

    $this->getAsTenant($tenant, '/customer/login')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tenant/customer/Login'));
});

test('authenticated customer is redirected away from login page', function () {
    $tenant = $this->createTenant('cauth-2', 'cauth-2.test');
    $customer = $this->createCustomerForTenant($tenant);

    $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, '/customer/login'))
        ->assertRedirect();
});

// ---------------------------------------------------------------------------
// Register page
// ---------------------------------------------------------------------------

test('customer can view the register page', function () {
    $tenant = $this->createTenant('cauth-3', 'cauth-3.test');

    $this->getAsTenant($tenant, '/customer/register')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tenant/customer/Register'));
});

test('authenticated customer is redirected away from register page', function () {
    $tenant = $this->createTenant('cauth-4', 'cauth-4.test');
    $customer = $this->createCustomerForTenant($tenant);

    $this->actingAs($customer, 'customer')
        ->get($this->tenantUrl($tenant, '/customer/register'))
        ->assertRedirect();
});

// ---------------------------------------------------------------------------
// Registration
// ---------------------------------------------------------------------------

test('customer can register with valid data', function () {
    $tenant = $this->createTenant('cauth-5', 'cauth-5.test');

    $response = $this->postAsTenant($tenant, '/customer/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/shop');

    $this->asTenant($tenant, function () {
        expect(Customer::where('email', 'jane@example.com')->exists())->toBeTrue();
    });
});

test('registering logs the customer in', function () {
    $tenant = $this->createTenant('cauth-6', 'cauth-6.test');

    $this->postAsTenant($tenant, '/customer/register', [
        'name' => 'Auto Login',
        'email' => 'autologin@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $customer = $this->asTenant($tenant, fn () => Customer::where('email', 'autologin@example.com')->first());

    $this->assertAuthenticatedAs($customer, 'customer');
});

test('registration fails when name is missing', function () {
    $tenant = $this->createTenant('cauth-7', 'cauth-7.test');

    $this->postAsTenant($tenant, '/customer/register', [
        'email' => 'noname@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('name');
});

test('registration fails when email is missing', function () {
    $tenant = $this->createTenant('cauth-8', 'cauth-8.test');

    $this->postAsTenant($tenant, '/customer/register', [
        'name' => 'No Email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('email');
});

test('registration fails when email is invalid', function () {
    $tenant = $this->createTenant('cauth-9', 'cauth-9.test');

    $this->postAsTenant($tenant, '/customer/register', [
        'name' => 'Bad Email',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('email');
});

test('registration fails when email is already taken in the same tenant', function () {
    $tenant = $this->createTenant('cauth-10', 'cauth-10.test');

    $this->asTenant($tenant, fn () => Customer::factory()->create(['email' => 'taken@example.com']));

    $this->postAsTenant($tenant, '/customer/register', [
        'name' => 'Duplicate',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('email');
});

test('registration fails when password is too short', function () {
    $tenant = $this->createTenant('cauth-11', 'cauth-11.test');

    $this->postAsTenant($tenant, '/customer/register', [
        'name' => 'Short Pass',
        'email' => 'short@example.com',
        'password' => '1234567',
        'password_confirmation' => '1234567',
    ])->assertSessionHasErrors('password');
});

test('registration fails when password confirmation does not match', function () {
    $tenant = $this->createTenant('cauth-12', 'cauth-12.test');

    $this->postAsTenant($tenant, '/customer/register', [
        'name' => 'Mismatch',
        'email' => 'mismatch@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different123',
    ])->assertSessionHasErrors('password');
});

test('same email can be registered in two different tenants', function () {
    [$tenantA, $tenantB] = $this->createTwoTenants();

    $sharedEmail = 'shared@example.com';

    $this->postAsTenant($tenantA, '/customer/register', [
        'name' => 'Customer A',
        'email' => $sharedEmail,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect('/shop');

    // Log out between requests so session doesn't interfere.
    $this->post($this->tenantUrl($tenantA, '/customer/logout'));

    $this->postAsTenant($tenantB, '/customer/register', [
        'name' => 'Customer B',
        'email' => $sharedEmail,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect('/shop');

    $countA = $this->asTenant($tenantA, fn () => Customer::where('email', $sharedEmail)->count());
    $countB = $this->asTenant($tenantB, fn () => Customer::where('email', $sharedEmail)->count());

    expect($countA)->toBe(1)
        ->and($countB)->toBe(1);
});

// ---------------------------------------------------------------------------
// Login
// ---------------------------------------------------------------------------

test('customer can login with valid credentials', function () {
    $tenant = $this->createTenant('cauth-13', 'cauth-13.test');
    $customer = $this->createCustomerForTenant($tenant, ['password' => 'secret1234']);

    $this->postAsTenant($tenant, '/customer/login', [
        'email' => $customer->email,
        'password' => 'secret1234',
    ])->assertRedirect('/shop');

    $this->assertAuthenticatedAs($customer, 'customer');
});

test('customer login fails with wrong password', function () {
    $tenant = $this->createTenant('cauth-14', 'cauth-14.test');
    $customer = $this->createCustomerForTenant($tenant);

    $this->postAsTenant($tenant, '/customer/login', [
        'email' => $customer->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('customer');
});

test('customer login fails with unknown email', function () {
    $tenant = $this->createTenant('cauth-15', 'cauth-15.test');

    $this->postAsTenant($tenant, '/customer/login', [
        'email' => 'nobody@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('customer');
});

test('customer login requires email field', function () {
    $tenant = $this->createTenant('cauth-16', 'cauth-16.test');

    $this->postAsTenant($tenant, '/customer/login', [
        'password' => 'password123',
    ])->assertSessionHasErrors('email');
});

test('customer login requires password field', function () {
    $tenant = $this->createTenant('cauth-17', 'cauth-17.test');

    $this->postAsTenant($tenant, '/customer/login', [
        'email' => 'someone@example.com',
    ])->assertSessionHasErrors('password');
});

// ---------------------------------------------------------------------------
// Logout
// ---------------------------------------------------------------------------

test('authenticated customer can logout', function () {
    $tenant = $this->createTenant('cauth-18', 'cauth-18.test');
    $customer = $this->createCustomerForTenant($tenant);

    $this->actingAs($customer, 'customer')
        ->post($this->tenantUrl($tenant, '/customer/logout'))
        ->assertRedirect('/shop');

    $this->assertGuest('customer');
});

test('guest cannot access logout endpoint', function () {
    $tenant = $this->createTenant('cauth-19', 'cauth-19.test');

    // A guest POST to logout should redirect (not 500/200).
    $this->postAsTenant($tenant, '/customer/logout')
        ->assertRedirect();
});

// ---------------------------------------------------------------------------
// Guard isolation — admin guard does not bleed into customer guard
// ---------------------------------------------------------------------------

test('tenant admin login does not authenticate as customer', function () {
    $tenant = $this->createTenant('cauth-20', 'cauth-20.test');

    // Create a tenant admin user via central users table.
    User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('adminpass'),
    ]);

    $this->postAsTenant($tenant, '/login', [
        'email' => 'admin@example.com',
        'password' => 'adminpass',
    ]);

    // Customer guard should still be a guest.
    $this->assertGuest('customer');
});
