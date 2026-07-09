<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Service\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function index(Request $request): View
    {
        $guest = $request->user();
        $wallets = $this->walletService->getWalletsForGuest($guest);

        return view('wallets.index', compact('wallets'));
    }

    public function create(): View
    {
        return view('wallets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $guest = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:bank,ewallet,cash,investment',
            'balance' => 'required|numeric|min:0',
        ]);

        $this->walletService->createWallet($guest, $validated);

        return redirect()->route('wallets.index')->with('success', 'Dompet berhasil dibuat.');
    }

    public function edit(Account $wallet): View
    {
        return view('wallets.edit', compact('wallet'));
    }

    public function update(Request $request, Account $wallet): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:bank,ewallet,cash,investment',
        ]);

        $this->walletService->updateWallet($wallet, $validated);

        return redirect()->route('wallets.index')->with('success', 'Dompet berhasil diperbarui.');
    }

    public function destroy(Account $wallet): RedirectResponse
    {
        $this->walletService->deleteWallet($wallet);

        return redirect()->route('wallets.index')->with('success', 'Dompet berhasil dihapus.');
    }
}
