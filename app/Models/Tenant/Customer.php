<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Scope a query to search customers by name or email.
     */
    public function scopeSearch($query, ?string $search)
    {
        // Trim whitespace and check if empty
        $search = trim($search ?? '');

        if ($search === '') {
            return $query;
        }

        // Escape SQL wildcards to treat them as literal characters
        $escapedSearch = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
        $lowerSearch = strtolower($escapedSearch);

        return $query->where(function ($q) use ($lowerSearch) {
            $q->whereRaw('LOWER(name) LIKE ?', ["%{$lowerSearch}%"])
                ->orWhereRaw('LOWER(email) LIKE ?', ["%{$lowerSearch}%"]);
        });
    }

    /**
     * Scope a query to sort customers by a given column and direction.
     */
    public function scopeSortBy($query, string $column, string $direction = 'asc')
    {
        $allowedColumns = ['name', 'email', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        $column = in_array($column, $allowedColumns) ? $column : 'created_at';
        $direction = in_array($direction, $allowedDirections) ? $direction : 'desc';

        return $query->orderBy($column, $direction);
    }
}
