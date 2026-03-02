<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added
        'user_id',
        'session_id',
        'product_id',
        'main_product_id',
        'quantity',
        'options',
        'price',
        'is_combo'
    ];

    protected $casts = [
        'options' => 'array',
        'is_combo' => 'boolean',
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter cart items by the active session tenant.
         * Ensures users/guests only see items for the store they are currently browsing.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($item) {
            /**
             * 2. Automatically assign the active tenant ID from session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($item->tenant_id)) {
                $item->tenant_id = $activeTenantId;
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship to the main product to access bundle rules.
     */
    public function mainProduct()
    {
        return $this->belongsTo(Product::class, 'main_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate subtotal for this line item.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->price * $this->quantity);
    }
}
