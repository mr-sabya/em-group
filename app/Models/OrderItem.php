<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added
        'order_id',
        'product_id',
        'product_variant_id',
        'item_name',
        'item_sku',
        'item_attributes',
        'quantity',
        'unit_price',
        'item_discount_amount',
        'item_tax_amount',
        'subtotal',
    ];

    protected $casts = [
        'item_attributes'      => 'array',
        'quantity'             => 'integer',
        'unit_price'           => 'decimal:2',
        'item_discount_amount' => 'decimal:2',
        'item_tax_amount'      => 'decimal:2',
        'subtotal'             => 'decimal:2',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter by active session tenant.
         * Ensures that when an admin is managing Store A, they don't see 
         * line items from Store B's orders.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($item) {
            /**
             * 2. Automatically assign the active tenant ID.
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get a formatted display of item attributes.
     */
    public function getFormattedAttributesAttribute(): string
    {
        if (empty($this->item_attributes)) {
            return '';
        }

        $formatted = [];
        foreach ($this->item_attributes as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }
        return implode(', ', $formatted);
    }

    /**
     * Total after line-item specific taxes and discounts.
     */
    public function getTotalPriceAttribute(): float
    {
        return (float) $this->subtotal;
    }
}
