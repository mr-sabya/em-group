<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PaymentMethod extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'image',
        'type',
        'instructions',
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

        /**
         * 1. Global Scope: Filter by the active session tenant.
         * Ensures Admin of Store A doesn't see Store B's payment details.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($method) {
            /**
             * 2. Automatically assign tenant_id from session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($method->tenant_id)) {
                $method->tenant_id = $activeTenantId;
            }

            if (empty($method->slug)) {
                $method->slug = Str::slug($method->name);
            }

            // If this is the first payment method for the store, make it default
            if (static::where('tenant_id', $method->tenant_id)->count() === 0) {
                $method->is_default = true;
            }
        });

        /**
         * 3. Scoped Default Management:
         * Ensure only one payment method is 'default' within the same store.
         */
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

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function shippingMethods()
    {
        return $this->belongsToMany(ShippingMethod::class, 'payment_method_shipping_method')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }
}
