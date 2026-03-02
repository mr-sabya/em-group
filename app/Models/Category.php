<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added tenant_id
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'icon',
        'is_active',
        'show_on_homepage',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_homepage' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // 1. Global Scope: Filter by the "Active" tenant in the session
        static::addGlobalScope('tenant', function (Builder $builder) {
            // We get the ID from the session because the user can switch tenants
            $activeTenantId = session('active_tenant_id');

            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($category) {
            // 2. Automatically assign the "Active" tenant ID
            $activeTenantId = session('active_tenant_id');

            if ($activeTenantId && empty($category->tenant_id)) {
                $category->tenant_id = $activeTenantId;
            }

            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug) && $category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relationship to the Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the parent category. 
     * Because of the Global Scope, this will only return a parent 
     * belonging to the same tenant.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withPivot('tenant_id');
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_category')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFeaturedOnHomepage($query)
    {
        return $query->where('show_on_homepage', true)->where('is_active', true);
    }

    public function scopeParentCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }
        if (str_starts_with($this->image, 'assets/')) {
            return asset($this->image);
        }
        return asset('storage/' . $this->image);
    }

    public function getIconUrlAttribute(): ?string
    {
        if (empty($this->icon)) {
            return null;
        }
        if (str_starts_with($this->icon, 'assets/')) {
            return asset($this->icon);
        }
        return asset('storage/' . $this->icon);
    }
}
