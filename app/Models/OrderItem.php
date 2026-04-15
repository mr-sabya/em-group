<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrderItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'product_id',
        'variant_id',
        'item_name',
        'option',
        'quantity',
        'price',
        'discount',
        'total'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'decimal:2',
        'discount' => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($activeTenantId = session('active_tenant_id')) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($item) {
            if (empty($item->tenant_id)) {
                $item->tenant_id = session('active_tenant_id');
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
