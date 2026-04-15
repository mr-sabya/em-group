<?php

namespace App\Enums;

enum OrderSource: string
{
    case LandingPage = 'landing_page';
    case Call = 'call';
    case WhatsApp = 'whatsapp';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case Others = 'others';
    case WooCommerce = 'woocommerce';
    case Ecommerce = 'ecommerce';

    public function label(): string
    {
        return match ($this) {
            self::LandingPage => 'Landing Page',
            self::Call => 'Phone Call',
            self::WhatsApp => 'WhatsApp',
            self::Facebook => 'Facebook',
            self::Instagram => 'Instagram',
            self::Others => 'Others',
            self::WooCommerce => 'WooCommerce',
            self::Ecommerce => 'Ecommerce',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LandingPage => 'heroicon-o-computer-desktop',
            self::Call => 'heroicon-o-phone',
            self::WhatsApp => 'heroicon-o-chat-bubble-left-right',
            self::Facebook => 'heroicon-o-share',
            self::Instagram => 'heroicon-o-camera',
            self::WooCommerce => 'heroicon-o-shopping-bag',
            default => 'heroicon-o-globe-alt',
        };
    }
}
