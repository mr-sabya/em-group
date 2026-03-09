<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Added
        'name',
        'slug',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // 1. Global Scope: Filter tags by the active session tenant.
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($tag) {
            // 2. Automatically assign the active tenant ID from session.
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($tag->tenant_id)) {
                $tag->tenant_id = $activeTenantId;
            }

            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if (empty($tag->slug) && $tag->isDirty('name')) {
                $tag->slug = Str::slug($tag->name);
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
     * Get the products that are associated with this tag.
     * Note: Pivot table 'product_tag' should also have 'tenant_id'.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tag')
            ->withPivot('tenant_id');
    }
}
