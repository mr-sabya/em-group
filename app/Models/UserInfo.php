<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'date_of_birth',
        'gender',
        'phone',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'zip_code',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Boot logic for slug generation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($info) {
            if (empty($info->slug) && $info->user) {
                $info->slug = Str::slug($info->user->name) . '-' . Str::random(5);
            }
        });
    }

    /* 
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
