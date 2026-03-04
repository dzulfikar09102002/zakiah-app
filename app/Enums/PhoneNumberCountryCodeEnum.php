<?php

namespace App\Enums;

enum PhoneNumberCountryCodeEnum: string
{
    // === ASEAN ===
    case INDONESIA = '+62';
    case MALAYSIA = '+60';
    case SINGAPORE = '+65';
    case THAILAND = '+66';
    case PHILIPPINES = '+63';
    case VIETNAM = '+84';
    case BRUNEI = '+673';
    case CAMBODIA = '+855';
    case LAOS = '+856';
    case MYANMAR = '+95';

    // === ASIA ===
    case CHINA = '+86';
    case JAPAN = '+81';
    case SOUTH_KOREA = '+82';
    case INDIA = '+91';
    case PAKISTAN = '+92';
    case BANGLADESH = '+880';
    case SRI_LANKA = '+94';
    case NEPAL = '+977';

    // === MIDDLE EAST ===
    case SAUDI_ARABIA = '+966';
    case UNITED_ARAB_EMIRATES = '+971';
    case QATAR = '+974';
    case KUWAIT = '+965';
    case OMAN = '+968';
    case TURKEY = '+90';

    // === EUROPE ===
    case UNITED_KINGDOM = '+44';
    case GERMANY = '+49';
    case FRANCE = '+33';
    case ITALY = '+39';
    case SPAIN = '+34';
    case NETHERLANDS = '+31';
    case BELGIUM = '+32';
    case SWITZERLAND = '+41';
    case SWEDEN = '+46';
    case NORWAY = '+47';
    case DENMARK = '+45';
    case POLAND = '+48';

    case UNITED_STATES = '+1';
    case MEXICO = '+52';
    case BRAZIL = '+55';
    case ARGENTINA = '+54';
    case CHILE = '+56';
    case COLOMBIA = '+57';
    case PERU = '+51';

    // === OCEANIA ===
    case AUSTRALIA = '+61';
    case NEW_ZEALAND = '+64';

    // === AFRICA ===
    case SOUTH_AFRICA = '+27';
    case EGYPT = '+20';
    case NIGERIA = '+234';
    case KENYA = '+254';
    case MOROCCO = '+212';

    public function label(): string
    {
        return str($this->name)
            ->replace('_', ' ')
            ->title()
            ->append(" ({$this->value})");
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