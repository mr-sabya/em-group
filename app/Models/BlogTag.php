<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added for multi-tenancy
        'name',
        'slug',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter by the active session tenant.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where('tenant_id', $activeTenantId);
            }
        });

        static::creating(function ($tag) {
            /**
             * 2. Automatically assign the active tenant ID from session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($tag->tenant_id)) {
                $tag->tenant_id = $activeTenantId;
            }

            $tag->slug = $tag->slug ?? Str::slug($tag->name);
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
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
     * A blog tag can belong to many blog posts.
     */
    public function blogPosts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_blog_tag')
            ->withPivot('tenant_id');
    }
}
