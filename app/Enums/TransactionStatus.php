<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Successful = 'successful';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartialRefund = 'partial_refund';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Successful => 'Successful',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
            self::PartialRefund => 'Partial Refund',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
