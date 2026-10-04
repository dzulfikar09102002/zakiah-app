<?php

namespace App\Enums;

enum LegalEntityTypeEnum: string
{
    case Cv = 'cv';
    case Pt = 'pt';
    case Ud = 'ud';
    case Perorangan = 'perorangan';

    public function label(): string
    {
        return match ($this) {
            self::Cv => 'CV',
            self::Pt => 'PT',
            self::Ud => 'UD',
            self::Perorangan => 'Perorangan',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])
            ->values()
            ->toArray();
    }
}
