<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added for multi-tenancy
        'name',
        'category_id',
        'title',
        'description',
        'featured_price',
        'image_path',
        'image_alt',
        'tag',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'featured_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter collections by the active store in session.
         * Ensures the admin only sees collections for the store they are managing.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($collection) {
            /**
             * 2. Automatically assign the active tenant ID from the session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($collection->tenant_id)) {
                $collection->tenant_id = $activeTenantId;
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

    /**
     * A collection belongs to a category.
     * Note: Since Category also has a tenant Global Scope, 
     * this will only return categories belonging to the current store.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            // Check if it's an external URL or a local storage path
            if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
                return $this->image_path;
            }
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}
