<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Pemasukan',
            self::Expense => 'Pengeluaran',
            self::Transfer => 'Transfer',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Income => '💰',
            self::Expense => '💸',
            self::Transfer => '🔄',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Income->value => self::Income->label(),
            self::Expense->value => self::Expense->label(),
            self::Transfer->value => self::Transfer->label(),
        ];
    }
}
