<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
            self::PartiallyRefunded => 'Partially Refunded',
        };
    }

    // ADD THIS METHOD
    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning text-dark',
            self::Paid => 'bg-success',
            self::Failed => 'bg-danger',
            self::Refunded => 'bg-info text-dark',
            self::PartiallyRefunded => 'bg-secondary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
