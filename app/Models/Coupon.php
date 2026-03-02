<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Import Builder
use App\Enums\CouponType;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added for multi-tenancy
        'code',
        'description',
        'type',
        'value',
        'min_spend',
        'max_discount_amount',
        'usage_limit_per_coupon',
        'usage_count',
        'usage_limit_per_user',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'type' => CouponType::class,
        'value' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit_per_coupon' => 'integer',
        'usage_count' => 'integer',
        'usage_limit_per_user' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter coupons by the active session tenant.
         * Ensures admins only see coupons for the store they are currently managing.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($coupon) {
            /**
             * 2. Automatically assign the active tenant ID.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($coupon->tenant_id)) {
                $coupon->tenant_id = $activeTenantId;
            }

            // 3. Auto-capitalize the code (e.g., "save10" -> "SAVE10")
            // This ensures consistency within the tenant's data.
            $coupon->code = strtoupper($coupon->code);
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
     * Get the orders that have used this coupon.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the users who are specifically allowed to use this coupon.
     * Note: Pivot table 'coupon_user' should ideally have 'tenant_id'.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /**
     * Get the products that this coupon applies to.
     * Note: Pivot table 'coupon_product' should ideally have 'tenant_id'.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'coupon_product')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /**
     * Get the categories that this coupon applies to.
     * Note: Pivot table 'coupon_category' should ideally have 'tenant_id'.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'coupon_category')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }
    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from') // Handle cases where start date might be null (if allowed)
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit_per_coupon')
                    ->orWhereColumn('usage_count', '<', 'usage_limit_per_coupon');
            });
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code)); // Ensure we search by uppercase
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the coupon is valid at the current time.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check date range
        $now = now();
        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        // Check global usage limit
        if ($this->usage_limit_per_coupon && $this->usage_count >= $this->usage_limit_per_coupon) {
            return false;
        }

        return true;
    }

    /**
     * Apply the discount to a given amount.
     */
    public function applyToAmount(float $amount): float
    {
        if (!$this->isValid() || $amount < ($this->min_spend ?? 0)) {
            return 0.00;
        }

        switch ($this->type) {
            case CouponType::Percentage:
                // Calculate percentage
                $discount = ($amount * $this->value) / 100;

                // Cap at max discount if set
                if ($this->max_discount_amount > 0 && $discount > $this->max_discount_amount) {
                    $discount = $this->max_discount_amount;
                }

                return round($discount, 2);

            case CouponType::FixedAmount:
                // Discount cannot exceed the order amount
                return round(min($this->value, $amount), 2);

            case CouponType::FreeShipping:
                // Usually returns 0 here because shipping cost is handled elsewhere in the cart logic
                return 0.00;

            default:
                return 0.00;
        }
    }
}
