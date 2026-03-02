<?php

namespace App\Enums;

enum CouponType: string
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';
    case FreeShipping = 'free_shipping';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage Discount',
            self::FixedAmount => 'Fixed Amount Discount',
            self::FreeShipping => 'Free Shipping',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    
}
