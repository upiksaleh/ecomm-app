<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenant;
use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

class DevTenantSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (tenant() == null) { // if there is no tenant, we are in central, so we can seed the demo tenant
            return;
        }
        $demo_id = 'demo';
        // $demo_tenant = Tenant::create(['id' => $demo_id]);
        // $demo_tenant->domains()->create(['domain' => $demo_id . '.localhost']);

        User::create([
            'name' => 'admin-'.$demo_id,
            'email' => 'admin-'.$demo_id.'@ecom.app',
            'password' => 'admin',
        ]);
    }
}
