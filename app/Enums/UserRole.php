<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Vendor = 'vendor';
    case Investor = 'investor';

    /**
     * Get a user-friendly label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Vendor => 'Vendor',
            self::Investor => 'Investor',
        };
    }

    /**
     * Check if the role is a customer.
     */
    public function isCustomer(): bool
    {
        return $this === self::Customer;
    }

    /**
     * Check if the role is a vendor.
     */
    public function isVendor(): bool
    {
        return $this === self::Vendor;
    }

    /**
     * Check if the role is an investor.
     */
    public function isInvestor(): bool
    {
        return $this === self::Investor;
    }

    /**
     * Get all role values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}