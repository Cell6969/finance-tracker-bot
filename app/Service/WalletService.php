<?php

namespace App\Service;

use App\Models\Account;
use App\Models\Guest;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function createWallet(Guest $guest, array $data): Account
    {
        return Account::create([
            'guest_id' => $guest->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'balance' => $data['balance'] ?? 0,
        ]);
    }

    public function updateWallet(Account $account, array $data): Account
    {
        $account->update($data);

        return $account;
    }

    public function deleteWallet(Account $account): bool
    {
        return $account->delete();
    }

    public function getWalletsForGuest(Guest $guest)
    {
        return $guest->accounts()->get();
    }

    public function updateBalance(Account $account, float $amount, string $operator = '+'): void
    {
        DB::transaction(function () use ($account, $amount, $operator) {
            $account->refresh();
            if ($operator === '+') {
                $account->increment('balance', $amount);
            } else {
                $account->decrement('balance', $amount);
            }
        });
    }
}
