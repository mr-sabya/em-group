<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AttributeSet extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // 1. Global Scope: Filter sets by the active session tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($attributeSet) {
            // 2. Automatically assign the active tenant ID from session
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($attributeSet->tenant_id)) {
                $attributeSet->tenant_id = $activeTenantId;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the attributes associated with this set.
     */
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_attribute_set')
            ->withPivot('tenant_id');
    }

    /**
     * Get the products that use this attribute set.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
