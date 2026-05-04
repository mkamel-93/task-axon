<?php

declare(strict_types=1);

namespace App\Enums;

enum PhoneStateEnum: string
{
    case Valid = 'valid';
    case Invalid = 'invalid';

    /**
     * Short display label shown in the table (OK / NOK).
     */
    public function label(): string
    {
        return match ($this) {
            self::Valid => 'OK',
            self::Invalid => 'NOK',
        };
    }

    /**
     * All countries as an array suitable for building <select> options:
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $state) => [
                'value' => $state->value,
                'label' => $state->value,
            ],
            self::cases()
        );
    }
}
