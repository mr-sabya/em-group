<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * 
     * Updated to match your schema: name, phone, email, password, avatar.
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // App\Models\User.php
    public function info()
    {
        return $this->hasOne(UserInfo::class);
    }

    /**
     * Business Relationships
     * Note: These work as long as the related tables have a 'user_id' or 'vendor_id'
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the URL to the user's avatar.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : asset('images/default_avatar.png');
    }

    /**
     * Check if a user has purchased a specific product.
     */
    public function hasPurchased($productId): bool
    {
        return $this->orders()
            ->where('order_status', 'delivered')
            ->whereHas('orderItems', fn($q) => $q->where('product_id', $productId))
            ->exists();
    }
}
