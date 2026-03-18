<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenant;
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
        $dev_tenant = Tenant::create(['id' => $dev_id]);
        $dev_tenant->domains()->create(['domain' => $dev_id . '.localhost']);
        
        tenancy()->initialize($dev_tenant);
        User::create([
            'name' => 'admin-'.$dev_id,
            'email' => 'admin-'.$dev_id.'@ecom.app',
            'password' => 'admin',
        ]);
        
        for ($i = 1; $i <= 100; $i++) {
            Product::create([
                'name' => 'Sample Product '.$i,
                'description' => 'This is a sample product '.$i,
                'price' => $i.'99',
                'sku' => 'SSP-00'.$i,
                'quantity' => 100,
                'active' => true,
            ]);
        }
    }
}
