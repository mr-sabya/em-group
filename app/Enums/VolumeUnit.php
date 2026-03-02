<?php

namespace App\Enums;

enum VolumeUnit: string
{
    case Litre = 'l';
    case Millilitre = 'ml';
    case CubicMetre = 'm3';
    case CubicCentimetre = 'cm3';
    case CubicInch = 'in3';
    case CubicFoot = 'ft3';

    /**
     * Returns the full display name (e.g., "Cubic Metres")
     */
    public function label(): string
    {
        return match ($this) {
            self::Litre => 'Litres',
            self::Millilitre => 'Millilitres',
            self::CubicMetre => 'Cubic Metres',
            self::CubicCentimetre => 'Cubic Centimetres',
            self::CubicInch => 'Cubic Inches',
            self::CubicFoot => 'Cubic Feet',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
