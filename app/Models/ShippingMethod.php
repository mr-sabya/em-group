<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status'     => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // 1. Global Scope: Filter by session active tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($method) {
            // 2. Automatically assign tenant_id
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($method->tenant_id)) {
                $method->tenant_id = $activeTenantId;
            }

            if (empty($method->slug)) {
                $method->slug = Str::slug($method->name);
            }

            // 3. If this is the first method for the tenant, make it default
            if (static::where('tenant_id', $method->tenant_id)->count() === 0) {
                $method->is_default = true;
            }
        });

        // 4. Ensure only one is_default per tenant when updating/creating
        static::saving(function ($method) {
            if ($method->is_default) {
                static::where('tenant_id', $method->tenant_id)
                    ->where('id', '!=', $method->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships & Scopes
    |--------------------------------------------------------------------------
    */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    public function scopeDefault(Builder $query)
    {
        return $query->where('is_default', true);
    }

    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'payment_method_shipping_method')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }
}
