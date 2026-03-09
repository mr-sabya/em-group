<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Traits\BelongsToTenant;

class Transaction extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Added
        'order_id',
        'payment_method',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'type',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'status' => TransactionStatus::class,
        'type' => TransactionType::class,
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 1. Global Scope: Filter transactions by the active session tenant.
         * Crucial for multi-store accounting and dashboard stats.
         */
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($transaction) {
            /**
             * 2. Automatically assign the active tenant ID from session.
             */
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($transaction->tenant_id)) {
                $transaction->tenant_id = $activeTenantId;
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSuccessful($query)
    {
        return $query->where('status', TransactionStatus::Successful);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', TransactionStatus::Failed);
    }

    public function scopePayments($query)
    {
        return $query->where('type', TransactionType::Payment);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', TransactionType::Refund);
    }
}
