<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevTenantSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demo_tenant = \App\Models\Tenant::create(['id' => 'demo']);
        $demo_tenant->domains()->create(['domain' => 'demo.localhost']);
    }
}
