<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Enums\ProductType;
use App\Enums\VolumeUnit;
use App\Enums\WeightUnit;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Added for multi-tenancy
        'brand_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'long_description',
        'thumbnail_image_path',
        'type',
        'regular_price',
        'sale_price',
        'retail_price',
        'distributor_price',
        'purchase_price',
        'weight',
        'weight_unit',
        'volume',
        'volume_unit',
        'is_active',
        'is_featured',
        'is_new',
        'is_manage_stock',
        'quantity',
        'min_order_quantity',
        'max_order_quantity',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image_path',
    ];

    protected $appends = ['thumbnail_url', 'current_stock', 'effective_price'];

    protected $casts = [
        'regular_price'     => 'decimal:2',
        'sale_price'        => 'decimal:2',
        'retail_price'      => 'decimal:2',
        'distributor_price' => 'decimal:2',
        'purchase_price'    => 'decimal:2',
        'weight'            => 'decimal:2',
        'weight_unit'       => WeightUnit::class,
        'volume'            => 'decimal:2',
        'volume_unit'       => VolumeUnit::class,
        'is_active'         => 'boolean',
        'is_featured'       => 'boolean',
        'is_new'            => 'boolean',
        'is_manage_stock'   => 'boolean',
        'quantity'           => 'integer',
        'min_order_quantity' => 'integer',
        'max_order_quantity' => 'integer',
        'type'               => ProductType::class,
    ];

    protected $attributes = [
        'type'      => ProductType::Normal,
        'is_active' => true,
        'is_new'    => true,
        'quantity'  => 0,
    ];

    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Automatically filter products by the active session tenant.
         * This allows one user to switch between different store views.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), session('active_tenant_id'));
            }
        });

        static::creating(function ($product) {
            /**
             * 2. Automatically assign the active tenant ID from the session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($product->tenant_id)) {
                $product->tenant_id = $activeTenantId;
            }

            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /* --- Relationships --- */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function brand()
    {
        // Because Brand also has a Global Scope, this will automatically 
        // return the brand belonging to the same tenant.
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        $tenantId = session('active_tenant_id');

        return $this->belongsToMany(Category::class, 'category_product')
            ->withPivot('tenant_id')
            ->where('categories.tenant_id', $tenantId)
            ->wherePivot('tenant_id', $tenantId);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_product')
            ->where('deals.tenant_id', session('active_tenant_id'));
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_product')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /* --- Accessors & Logic --- */

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_image_path ? asset('storage/' . $this->thumbnail_image_path) : asset('images/placeholder.png');
    }

    public function getCurrentStockAttribute(): int
    {
        return $this->isVariable() ? (int) $this->variants()->sum('quantity') : (int) $this->quantity;
    }

    public function getEffectivePriceAttribute()
    {
        if (!$this->exists) {
            return $this->sale_price ?? $this->regular_price;
        }

        $basePrice = $this->sale_price ?? $this->regular_price;

        $activeDeal = $this->deals()
            ->where('deals.is_active', true)
            ->first();

        if ($activeDeal) {
            if ($activeDeal->type === 'percentage') {
                $basePrice = $basePrice * (1 - ($activeDeal->value / 100));
            } elseif ($activeDeal->type === 'fixed') {
                $basePrice = max(0, $basePrice - $activeDeal->value);
            }
        }

        return $basePrice;
    }

    public function isNormal(): bool
    {
        return $this->type === ProductType::Normal;
    }

    public function isVariable(): bool
    {
        return $this->type === ProductType::Variable;
    }

    /* --- Scopes --- */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    public function syncCategoriesWithTenant(array $categoryIds)
    {
        $tenantId = session('active_tenant_id');

        $data = [];

        foreach ($categoryIds as $id) {
            $data[$id] = ['tenant_id' => $tenantId];
        }

        $this->categories()->sync($data);
    }
}
