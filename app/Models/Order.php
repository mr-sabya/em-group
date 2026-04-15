<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderSource;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'admin_id',
        'user_id',
        'courier_id',
        'order_number',

        // Customer
        'name',
        'phone',
        'email',
        'address',
        'district',
        'latitude',
        'longitude',

        'customer_note',
        'courier_note',
        'source',

        // Financials
        'subtotal',
        'delivery_fee',
        'discount',
        'coupon_discount',
        'total_amount',
        'paid_amount',
        'coupon_code',

        // Payment
        'payment_method_id',
        'payment_method_name',
        'transaction_id',
        'payment_phone_number',
        'payment_status',

        // Shipping
        'shipping_method_id',
        'shipping_method_name',
        'tracking_number',

        'status',

        // Timestamps
        'placed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',

        // Cancellation
        'cancel_reason_id',
        'cancel_note',
        'cancelled_by_admin_id',
        'cancelled_by_user_id'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'source' => OrderSource::class,
        'placed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($activeTenantId = session('active_tenant_id')) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($order) {
            if (empty($order->tenant_id)) {
                $order->tenant_id = session('active_tenant_id');
            }

            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Ymd') . '-' . Str::upper(Str::random(6));
            }

            // Fix for the "Column not found: placed_at" error
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

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
    public function cancelReason()
    {
        return $this->belongsTo(CancelReason::class);
    }
    public function cancelledByAdmin()
    {
        return $this->belongsTo(Admin::class, 'cancelled_by_admin_id');
    }
    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
