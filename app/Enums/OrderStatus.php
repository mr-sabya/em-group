<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded'; // Could be tied to PaymentStatus Refunded, but for overall order state

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning',
            self::Processing => 'bg-info',
            self::Shipped => 'bg-primary',
            self::Delivered => 'bg-success',
            self::Cancelled => 'bg-danger',
            self::Refunded => 'bg-secondary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
