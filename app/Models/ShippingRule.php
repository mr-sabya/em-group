<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ShippingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added
        'shipping_method_id',
        'country_id',
        'state_id',
        'city_id',
        'cost'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter shipping rules by the active session tenant.
         * Ensures the admin of Store A only manages their own shipping rates.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($rule) {
            /**
             * 2. Automatically assign the active tenant ID from session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($rule->tenant_id)) {
                $rule->tenant_id = $activeTenantId;
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

    public function method()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
