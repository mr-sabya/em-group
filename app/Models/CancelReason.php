<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CancelReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
