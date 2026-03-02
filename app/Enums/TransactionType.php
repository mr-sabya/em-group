<?php

namespace App\Enums;

enum TransactionType: string
{
    case Payment = 'payment';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Payment => 'Payment',
            self::Refund => 'Refund',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}