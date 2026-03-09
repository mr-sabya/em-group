<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use App\Traits\BelongsToTenant;

class Order extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Added for multi-tenancy
        'user_id',
        'order_number',

        // Billing
        'billing_first_name',
        'billing_last_name',
        'billing_email',
        'billing_phone',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_country_id',
        'billing_state_id',
        'billing_city_id',
        'billing_zip_code',

        // Shipping
        'shipping_first_name',
        'shipping_last_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_country_id',
        'shipping_state_id',
        'shipping_city_id',
        'shipping_zip_code',

        // Money
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'tax_amount',
        'total_amount',
        'currency',

        // Payment
        'payment_method_id',
        'payment_method_name',
        'transaction_id',
        'payment_phone_number',
        'payment_status',

        // Shipping Logistics
        'shipping_method_id',
        'shipping_method_name',
        'order_status',
        'tracking_number',

        'notes',
        'placed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancel_reason',
        'cancelled_by',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost'   => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'payment_status'  => PaymentStatus::class,
        'order_status'    => OrderStatus::class,
        'placed_at'       => 'datetime',
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter orders by the active session tenant.
         * Ensures that when the admin switches stores, they only see relevant orders.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($order) {
            /**
             * 2. Automatically assign the active tenant ID from session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($order->tenant_id)) {
                $order->tenant_id = $activeTenantId;
            }

            /**
             * 3. Generate Order Number
             * Scoped unique constraint in migration allows #ORD-1001 to exist 
             * in multiple stores simultaneously.
             */
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Ymd') . '-' . Str::upper(Str::random(6));
            }

            if (empty($order->placed_at)) {
                $order->placed_at = now();
            }
        });
    }

    /* 
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relationship to the Store/Tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Address Relationships
    public function shippingCountry()
    {
        return $this->belongsTo(Country::class, 'shipping_country_id');
    }
    public function shippingState()
    {
        return $this->belongsTo(State::class, 'shipping_state_id');
    }
    public function shippingCity()
    {
        return $this->belongsTo(City::class, 'shipping_city_id');
    }

    /* 
    |--------------------------------------------------------------------------
    | Logic & Helpers
    |--------------------------------------------------------------------------
    */

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    public function canBeCancelled(): bool
    {
        return $this->order_status === OrderStatus::Pending;
    }

    /* 
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the full shipping address formatted as a string.
     */
    public function getFullShippingAddressAttribute(): string
    {
        $address = "{$this->shipping_address_line_1}";
        if ($this->shipping_address_line_2) $address .= ", {$this->shipping_address_line_2}";

        $city = $this->shippingCity?->name ?? 'Unknown City';
        $state = $this->shippingState?->name ?? 'Unknown State';
        $country = $this->shippingCountry?->name ?? 'Unknown Country';

        return "{$address}, {$city}, {$state} {$this->shipping_zip_code}, {$country}";
    }

    /**
     * Get the customer name (User name or Billing name if guest).
     */
    public function getCustomerNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->billing_first_name . ' ' . $this->billing_last_name . ' (Guest)';
    }
}
