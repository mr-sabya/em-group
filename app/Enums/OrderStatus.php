<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Followup = 'followup';
    case Confirmed = 'confirmed';

    /** 
     * We use 'Cancelled' for the PHP constant to fix the error, 
     * but keep 'canceled' as the string value to match your HTML/Database.
     */
    case Cancelled = 'canceled';

    case ReadyToShip = 'ready_to_ship';
    case Shipped = 'shipped';
    case HoldByCourier = 'hold-by-courier';
    case Delivered = 'delivered';
    case PaymentReceived = 'payment-received';
    case Returned = 'returned';
    case Unresolved = 'unresolved';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Followup => 'Followup',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Canceled', // Display label
            self::ReadyToShip => 'Ready To Ship',
            self::Shipped => 'Shipped',
            self::HoldByCourier => 'Hold By Courier',
            self::Delivered => 'Delivered',
            self::PaymentReceived => 'Payment Received',
            self::Returned => 'Returned',
            self::Unresolved => 'Unresolved',
        };
    }

    /**
     * Color mapping for badges
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending, self::Followup => 'warning',
            self::Confirmed => 'primary',
            self::ReadyToShip, self::Shipped => 'info',
            self::Delivered, self::PaymentReceived => 'success',
            self::Cancelled, self::Returned, self::Unresolved, self::HoldByCourier => 'danger',
        };
    }

    /**
     * Helper for <select> options
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $status) {
            $options[$status->value] = $status->label();
        }
        return $options;
    }
}
