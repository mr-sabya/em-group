<?php

namespace App\Enums;

enum SettingType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Json = 'json';
    case Text = 'text';
    case Email = 'email';
    case Url = 'url';
    case Image = 'image';
    case Color = 'color';
    case Password = 'password'; // For sensitive strings that might need special handling (e.g., masking in UI)

    public function label(): string
    {
        return match ($this) {
            self::String => 'Text Input',
            self::Integer => 'Number Input',
            self::Boolean => 'Checkbox',
            self::Json => 'JSON Text',
            self::Text => 'Textarea',
            self::Email => 'Email Address',
            self::Url => 'URL',
            self::Image => 'Image Upload',
            self::Color => 'Color Picker',
            self::Password => 'Password',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
