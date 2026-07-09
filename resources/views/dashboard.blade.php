@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Saldo</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalBalance, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-full">
                    <i class="fas fa-wallet text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pemasukan Bulan Ini</p>
                    <p class="text-2xl font-bold text-green-600">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-arrow-down text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pengeluaran Bulan Ini</p>
                    <p class="text-2xl font-bold text-red-600">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-arrow-up text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Cash Flow</p>
                    <p class="text-2xl font-bold {{ $cashFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($cashFlow, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 {{ $cashFlow >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line {{ $cashFlow >= 0 ? 'text-green-600' : 'text-red-600' }} text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Summary -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Wallet Summary</h2>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @forelse($wallets as $wallet)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                @if($wallet->type === 'bank') bg-blue-100
                                @elseif($wallet->type === 'ewallet') bg-purple-100
                                @elseif($wallet->type === 'investment') bg-yellow-100
                                @else bg-gray-100
                                @endif">
                                <i class="fas
                                    @if($wallet->type === 'bank') fa-university text-blue-600
                                    @elseif($wallet->type === 'ewallet') fa-mobile-alt text-purple-600
                                    @elseif($wallet->type === 'investment') fa-chart-line text-yellow-600
                                    @else fa-money-bill-wave text-gray-600
                                @endif"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $wallet->name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($wallet->type) }}</p>
                            </div>
                        </div>
                        <p class="font-semibold text-gray-900">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">Belum ada wallet. <a href="{{ route('wallets.create') }}" class="text-indigo-600 hover:underline">Buat wallet baru</a></p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h2>
            <a href="{{ route('transactions.index') }}" class="text-sm text-indigo-600 hover:underline">Lihat Semua</a>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @forelse($recentTransactions as $transaction)
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                @if($transaction->type === 'income') bg-green-100
                                @elseif($transaction->type === 'expense') bg-red-100
                                @else bg-blue-100
                            @endif">
                                <i class="fas
                                    @if($transaction->type === 'income') fa-arrow-down text-green-600
                                    @elseif($transaction->type === 'expense') fa-arrow-up text-red-600
                                    @else fa-exchange-alt text-blue-600
                                @endif"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">
                                    @if($transaction->type === 'transfer')
                                        Transfer ke {{ $transaction->transfer?->toAccount?->name ?? '?' }}
                                    @else
                                        {{ $transaction->category?->name ?? 'Tanpa Kategori' }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $transaction->account?->name ?? '?' }}
                                    @if($transaction->description)
                                        &middot; {{ $transaction->description }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold
                                @if($transaction->type === 'income') text-green-600
                                @elseif($transaction->type === 'expense') text-red-600
                                @else text-blue-600
                            @endif">
                                @if($transaction->type === 'expense')-@endif
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $transaction->transaction_at->format('d M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
