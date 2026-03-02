<?php

namespace App\Enums;

enum ProductType: string
{
    case Normal = 'normal';
    case Variable = 'variable';
    
    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal Product',
            self::Variable => 'Variable Product',
        };
    }

    public function isNormal(): bool
    {
        return $this === self::Normal;
    }
    public function isVariable(): bool
    {
        return $this === self::Variable;
    }
   

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
