<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenant;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

class DevTenantSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $dev_id = 'dev';
        $dev_tenant = Tenant::where('id', $dev_id)->first();
        if (! $dev_tenant) {
            $dev_tenant = Tenant::create(['id' => $dev_id]);
            $dev_tenant->domains()->create(['domain' => $dev_id.'.'.env('APP_DOMAIN', 'localhost')]);
        }

        tenancy()->initialize($dev_tenant);

        User::create([
            'name' => 'admin-'.$dev_id,
            'email' => 'admin-'.$dev_id.'@ecom.app',
            'password' => 'admin',
        ]);

        Product::factory(100)->create();

        Customer::factory(100)->create();
    }
}
