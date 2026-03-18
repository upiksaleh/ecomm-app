<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Get the count of products for this tenant.
     */
    public function getProductsCountAttribute(): int
    {
        return $this->run(function () {
            return DB::table('products')->count();
        });
    }

    /**
     * Get the count of customers for this tenant.
     */
    public function getCustomersCountAttribute(): int
    {
        return $this->run(function () {
            return DB::table('customers')->count();
        });
    }
}
