<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',    // Added for multi-tenancy
        'attribute_id',
        'value',
        'slug',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter values by the active session tenant.
         * This ensures that when an admin switches stores, they only see the 
         * attribute values (like specific colors or sizes) for that store.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($attributeValue) {
            /**
             * 2. Automatically assign the active tenant ID from the session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($attributeValue->tenant_id)) {
                $attributeValue->tenant_id = $activeTenantId;
            }

            // Slug generation
            if (empty($attributeValue->slug)) {
                $attributeValue->slug = Str::slug($attributeValue->value);
            }
        });

        static::updating(function ($attributeValue) {
            if (empty($attributeValue->slug) && $attributeValue->isDirty('value')) {
                $attributeValue->slug = Str::slug($attributeValue->value);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relationship to the Tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the attribute that this value belongs to.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the products that have this attribute value as a specification.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_value');
    }

    /**
     * Get the product variants that use this attribute value.
     */
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attribute_value')
            ->withPivot('tenant_id');
    }
}
