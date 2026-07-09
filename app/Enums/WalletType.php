<?php

namespace App\Enums;

enum WalletType: string
{
    case Bank = 'bank';
    case Ewallet = 'ewallet';
    case Cash = 'cash';
    case Investment = 'investment';

    public function label(): string
    {
        return match ($this) {
            self::Bank => 'Bank',
            self::Ewallet => 'E-Wallet',
            self::Cash => 'Cash',
            self::Investment => 'Investasi',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Bank->value => self::Bank->label(),
            self::Ewallet->value => self::Ewallet->label(),
            self::Cash->value => self::Cash->label(),
            self::Investment->value => self::Investment->label(),
        ];
    }
}
