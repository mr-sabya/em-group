<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\WeightUnit;
use App\Enums\VolumeUnit;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', // Multi-tenancy
        'product_id',
        'sku',
        'thumbnail_image_path',

        // Prices
        'regular_price',
        'sale_price',
        'retail_price',
        'distributor_price',
        'purchase_price',

        // Stock management
        'is_manage_stock',
        'quantity',
        'min_order_quantity',
        'max_order_quantity',

        // Specs
        'weight',
        'weight_unit',
        'volume',
        'volume_unit',

        'is_active',
    ];

    protected $casts = [
        // Prices
        'regular_price'     => 'decimal:2',
        'sale_price'        => 'decimal:2',
        'retail_price'      => 'decimal:2',
        'distributor_price' => 'decimal:2',
        'purchase_price'    => 'decimal:2',

        // Specs
        'weight'            => 'decimal:2',
        'weight_unit'       => WeightUnit::class,
        'volume'            => 'decimal:2',
        'volume_unit'       => VolumeUnit::class,

        // Stock & Status
        'is_manage_stock'   => 'boolean',
        'is_active'         => 'boolean',
        'quantity'           => 'integer',
        'min_order_quantity' => 'integer',
        'max_order_quantity' => 'integer',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // 1. Global Scope: Filter variants by the active session tenant.
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($variant) {
            // 2. Automatically assign the active tenant ID from the session.
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($variant->tenant_id)) {
                $variant->tenant_id = $activeTenantId;
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

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_value')
            ->withPivot('tenant_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Logic
    |--------------------------------------------------------------------------
    */

    /**
     * Get the effective price (Sale price if available, else regular).
     */
    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->regular_price;
    }

    /**
     * Get the main thumbnail image URL.
     * Priorities: 1. Variant Thumbnail Path, 2. First Variant Image, 3. Parent Product Thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_image_path) {
            return asset('storage/' . $this->thumbnail_image_path);
        }

        $thumbnail = $this->images()->where('is_thumbnail', true)->first();
        if ($thumbnail) {
            return asset('storage/' . $thumbnail->image_path);
        }

        return $this->product->thumbnail_url ?? asset('images/placeholder.png');
    }

    /**
     * Get a descriptive name for the variant (e.g., "Red, Large").
     */
    public function getDisplayNameAttribute(): string
    {
        // Pluck the values and join them with a comma
        return $this->attributeValues->pluck('value')->implode(', ');
    }

    /**
     * Scope for active variants.
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}
