<?php

namespace App\Enums;

enum WeightUnit: string
{
    case Gram = 'g';
    case Kilogram = 'kg';
    case Pound = 'lb';
    case Ounce = 'oz';
    case Tonne = 't';

    /**
     * Returns the full display name (e.g., "Kilograms")
     */
    public function label(): string
    {
        return match ($this) {
            self::Gram => 'Grams',
            self::Kilogram => 'Kilograms',
            self::Pound => 'Pounds',
            self::Ounce => 'Ounces',
            self::Tonne => 'Tonnes',
        };
    }

    /**
     * Returns all cases formatted for a select dropdown
     */
    public static function options(): array
    {
        return array_map(fn($unit) => [
            'value' => $unit->value,
            'label' => $unit->label(),
        ], self::cases());
    }
}
