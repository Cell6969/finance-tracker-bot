<?php

namespace App\Enums;

enum CategoryType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Pemasukan',
            self::Expense => 'Pengeluaran',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Income->value => self::Income->label(),
            self::Expense->value => self::Expense->label(),
        ];
    }
}
