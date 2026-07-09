<?php

namespace App\Service;

use App\Models\Guest;

class DashboardService
{
    public function getDashboardData(Guest $guest): array
    {
        $totalBalance = $guest->accounts()->sum('balance');

        $currentMonth = now()->startOfMonth();
        $monthlyIncome = $guest->transactions()
            ->where('type', 'income')
            ->where('transaction_at', '>=', $currentMonth)
            ->sum('amount');

        $monthlyExpense = $guest->transactions()
            ->where('type', 'expense')
            ->where('transaction_at', '>=', $currentMonth)
            ->sum('amount');

        $wallets = $guest->accounts()->get()->map(function ($wallet) {
            return [
                'id' => $wallet->id,
                'name' => $wallet->name,
                'type' => $wallet->type,
                'type_label' => $wallet->typeLabel(),
                'balance' => (float) $wallet->balance,
            ];
        });

        $recentTransactions = $guest->transactions()
            ->with(['category', 'account'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'type_label' => $transaction->typeLabel(),
                    'type_icon' => $transaction->typeIcon(),
                    'amount' => (float) $transaction->amount,
                    'description' => $transaction->description,
                    'category' => $transaction->category?->name,
                    'account' => $transaction->account?->name,
                    'date' => $transaction->transaction_at->format('d M Y'),
                ];
            });

        $cashFlow = $this->getCashFlow($guest, 6);

        return [
            'total_balance' => (float) $totalBalance,
            'monthly_income' => (float) $monthlyIncome,
            'monthly_expense' => (float) $monthlyExpense,
            'wallets' => $wallets,
            'recent_transactions' => $recentTransactions,
            'cash_flow' => $cashFlow,
        ];
    }

    private function getCashFlow(Guest $guest, int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $income = $guest->transactions()
                ->where('type', 'income')
                ->whereBetween('transaction_at', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $expense = $guest->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_at', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $data[] = [
                'month' => $date->format('M Y'),
                'income' => (float) $income,
                'expense' => (float) $expense,
            ];
        }

        return $data;
    }
}
