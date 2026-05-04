<?php

declare(strict_types=1);

namespace App\Enums;

enum CountryEnum: string
{
    case Cameroon = '237';
    case Ethiopia = '251';
    case Morocco = '212';
    case Mozambique = '258';
    case Uganda = '256';

    public function label(): string
    {
        return $this->name;
    }

    public function regex(): string
    {
        return match ($this) {
            self::Cameroon => '/\(237\)\ ?[2368]\d{7,8}$/',
            self::Ethiopia => '/\(251\)\ ?[1-59]\d{8}$/',
            self::Morocco => '/\(212\)\ ?[5-9]\d{8}$/',
            self::Mozambique => '/\(258\)\ ?[28]\d{7,8}$/',
            self::Uganda => '/\(256\)\ ?\d{9}$/',
        };
    }

    /**
     * Try to resolve a CountryEnum from a raw numeric code string.
     * Returns null when the code is unknown.
     */
    public static function tryFromCode(string $code): ?self
    {
        return self::tryFrom($code);
    }

    /**
     * All countries as an array suitable for building <select> options:
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $country) => [
                'value' => $country->value,
                'label' => $country->label(),
            ],
            self::cases()
        );
    }
}
