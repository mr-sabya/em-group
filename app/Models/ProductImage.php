<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ProductImage extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',          // Added for multi-tenancy
        'product_id',
        'product_variant_id',
        'image_path',
        'is_thumbnail',       // Added from migration
        'sort_order',
    ];

    protected $casts = [
        'sort_order'   => 'integer',
        'is_thumbnail' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter images by the active tenant in the session.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        /**
         * 2. Automatically assign the active tenant ID from the session on creation.
         */
        static::creating(function ($image) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($image->tenant_id)) {
                $image->tenant_id = $activeTenantId;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the tenant this image belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the product that the image belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant that the image belongs to (if any).
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeThumbnails(Builder $query)
    {
        return $query->where('is_thumbnail', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the full URL to the image.
     * Usage: $image->image_url
     */
    public function getImageUrlAttribute(): string
    {
        // Handle potential external URLs or standard storage paths
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }
}
