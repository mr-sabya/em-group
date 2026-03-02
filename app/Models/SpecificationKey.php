<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class SpecificationKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added
        'name',
        'slug',
        'group'
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter keys by the active session tenant.
         * Ensures Store A's technical labels don't clutter Store B's product forms.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($key) {
            /**
             * 2. Automatically assign the active tenant ID from session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($key->tenant_id)) {
                $key->tenant_id = $activeTenantId;
            }

            if (empty($key->slug)) {
                $key->slug = Str::slug($key->name);
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

    public function productSpecifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }
}
