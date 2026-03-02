<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductSpecification extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added for multi-tenancy
        'product_id',
        'specification_key_id',
        'value'
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter specifications by the active session tenant.
         * Ensures that when an admin is managing Store A, they don't see
         * technical specs belonging to Store B's products.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        /**
         * 2. Automatically assign the active tenant ID from session on creation.
         */
        static::creating(function ($specification) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($specification->tenant_id)) {
                $specification->tenant_id = $activeTenantId;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the tenant this specification belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the product associated with the specification.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the specification key (label) for this value.
     */
    public function key()
    {
        return $this->belongsTo(SpecificationKey::class, 'specification_key_id');
    }
}
