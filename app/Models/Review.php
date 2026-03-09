<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Ensure this is imported
use App\Enums\ReviewStatus;
use App\Traits\BelongsToTenant;

class Review extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Added
        'user_id',
        'product_id',
        'rating',
        'comment',
        'is_approved',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'status' => ReviewStatus::class,
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter reviews by the active session tenant.
         * Ensures Admin of Store A only moderates reviews for Store A.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($review) {
            /**
             * 2. Automatically assign the active tenant ID from session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($review->tenant_id)) {
                $review->tenant_id = $activeTenantId;
            }
        });

        static::saving(function ($review) {
            // Ensure rating is within 1-5 range.
            $review->rating = max(1, min(5, (int) $review->rating));
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeApproved($query)
    {
        return $query->where('status', ReviewStatus::Approved);
    }

    public function scopePending($query)
    {
        return $query->where('status', ReviewStatus::Pending);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the review has been approved.
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === ReviewStatus::Approved;
    }
}
