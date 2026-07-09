<?php

namespace App\Service;

use App\Models\Account;
use App\Models\Guest;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        private WalletService $walletService,
    ) {}

    public function createIncome(Guest $guest, array $data): Transaction
    {
        return DB::transaction(function () use ($guest, $data) {
            $transaction = Transaction::create([
                'guest_id' => $guest->id,
                'account_id' => $data['account_id'],
                'category_id' => $data['category_id'],
                'type' => 'income',
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'transaction_at' => $data['transaction_at'] ?? now(),
            ]);

            $this->walletService->updateBalance(
                Account::findOrFail($data['account_id']),
                $data['amount'],
                '+'
            );

            return $transaction;
        });
    }

    public function createExpense(Guest $guest, array $data): Transaction
    {
        return DB::transaction(function () use ($guest, $data) {
            $transaction = Transaction::create([
                'guest_id' => $guest->id,
                'account_id' => $data['account_id'],
                'category_id' => $data['category_id'],
                'type' => 'expense',
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'transaction_at' => $data['transaction_at'] ?? now(),
            ]);

            $this->walletService->updateBalance(
                Account::findOrFail($data['account_id']),
                $data['amount'],
                '-'
            );

            return $transaction;
        });
    }

    public function createTransfer(Guest $guest, array $data): Transfer
    {
        return DB::transaction(function () use ($guest, $data) {
            $transaction = Transaction::create([
                'guest_id' => $guest->id,
                'account_id' => $data['from_account_id'],
                'category_id' => null,
                'type' => 'transfer',
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'transaction_at' => $data['transaction_at'] ?? now(),
            ]);

            $transfer = Transfer::create([
                'guest_id' => $guest->id,
                'from_account_id' => $data['from_account_id'],
                'to_account_id' => $data['to_account_id'],
                'amount' => $data['amount'],
                'transaction_id' => $transaction->id,
            ]);

            $this->walletService->updateBalance(
                Account::findOrFail($data['from_account_id']),
                $data['amount'],
                '-'
            );

            $this->walletService->updateBalance(
                Account::findOrFail($data['to_account_id']),
                $data['amount'],
                '+'
            );

            return $transfer;
        });
    }

    public function getRecentTransactions(Guest $guest, int $limit = 10)
    {
        return $guest->transactions()
            ->with(['category', 'account'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getTransactionsByDateRange(Guest $guest, string $startDate, string $endDate)
    {
        return $guest->transactions()
            ->with(['category', 'account'])
            ->whereBetween('transaction_at', [$startDate, $endDate])
            ->latest()
            ->get();
    }
}
