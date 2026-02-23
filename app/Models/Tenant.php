<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant
{
    use HasDomains; // This provides the domains() method

    // Allow these fields to be fillable
    protected $fillable = ['id', 'name'];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
