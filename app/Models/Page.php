<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Page extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'page_type',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_robots',
        'og_image',
        'content',
        'published_at',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'content' => 'array',        // Converts longText JSON to a PHP array
        'published_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the tenant that owns the page.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include published pages that are past their start date.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Helper to get SEO data with fallbacks.
     */
    public function getSeoDataAttribute(): array
    {
        return [
            'title' => $this->meta_title ?: $this->title,
            'description' => $this->meta_description,
            'keywords' => $this->meta_keywords,
            'robots' => $this->meta_robots ?: 'index, follow',
            'og_image' => $this->og_image ? asset('storage/' . $this->og_image) : asset('default-og.png'),
        ];
    }

    /**
     * Determine if the page is currently live.
     */
    public function getIsLiveAttribute(): bool
    {
        return $this->status === 'published' &&
            $this->published_at &&
            $this->published_at->isPast();
    }
}
