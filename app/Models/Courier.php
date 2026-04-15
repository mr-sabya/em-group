<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', // Added
        'name',
        'is_active',
        'vendor',
        'credentials',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'array',
    ];

    /**
     * Relationship to Tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include active couriers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
