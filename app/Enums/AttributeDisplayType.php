<?php

namespace App\Enums;

enum AttributeDisplayType: string
{
    case Text = 'text';
    case Color = 'color';
    case Image = 'image';
    case Select = 'select'; // For dropdowns, radio buttons etc.
    case Checkbox = 'checkbox';
    case Number = 'number';
    case Date = 'date';

    public static function labels(): array
    {
        return [
            self::Text->value => 'Text Input',
            self::Color->value => 'Color Swatch',
            self::Image->value => 'Image Upload',
            self::Select->value => 'Dropdown / Select',
            self::Checkbox->value => 'Checkbox',
            self::Number->value => 'Number Input',
            self::Date->value => 'Date Picker',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }
}
