<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Enums\AttributeDisplayType;
use Illuminate\Database\Eloquent\Builder;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added for multi-tenancy
        'name',
        'slug',
        'display_type',
        'is_filterable',
        'is_active',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_active' => 'boolean',
        'display_type' => AttributeDisplayType::class,
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Automatically filter attributes by the active session tenant.
         * This allows an admin to manage different attribute sets for different stores.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($attribute) {
            /**
             * 2. Automatically assign the active tenant ID from the session context.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($attribute->tenant_id)) {
                $attribute->tenant_id = $activeTenantId;
            }

            // Slug generation
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });

        static::updating(function ($attribute) {
            if (empty($attribute->slug) && $attribute->isDirty('name')) {
                $attribute->slug = Str::slug($attribute->name);
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
     * Get the values for this attribute (e.g., "Red", "Blue" for "Color").
     * Since AttributeValue will also have a Global Scope, this stays isolated.
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    /**
     * Get the attribute sets that this attribute belongs to.
     */
    public function attributeSets()
    {
        return $this->belongsToMany(AttributeSet::class, 'attribute_attribute_set')
            ->withPivot('tenant_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
