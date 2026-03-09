<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case OnHold = 'on_hold';
    case Approved = 'approved';
    case Processing = 'processing';
    case ReadyToShip = 'ready_to_ship';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Flagged = 'flagged';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::OnHold => 'On Hold',
            self::Approved => 'Approved',
            self::Processing => 'Processing',
            self::ReadyToShip => 'Ready To Ship',
            self::InTransit => 'In-Transit',
            self::Delivered => 'Delivered',
            self::Flagged => 'Flagged',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning text-dark',
            self::OnHold => 'bg-secondary',
            self::Approved => 'bg-success',
            self::Processing => 'bg-info text-dark',
            self::ReadyToShip => 'bg-primary',
            self::InTransit => 'bg-primary',
            self::Delivered => 'bg-success',
            self::Flagged => 'bg-danger',
            self::Cancelled => 'bg-danger',
            self::Refunded => 'bg-dark',
        };
    }

    /**
     * Optional: Helper to get specific colors for the ERP tab style 
     * if you want to use custom hex colors later.
     */
    public function tabColor(): string
    {
        return match ($this) {
            self::Pending => '#17a2b8', // The teal color from your image
            default => '#6c757d',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
