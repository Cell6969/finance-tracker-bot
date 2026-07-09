@extends('layouts.app')

@section('title', 'Wallets')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Wallets</h1>
        <a href="{{ route('wallets.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">
            <i class="fas fa-plus mr-1"></i> Tambah Wallet
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($wallets as $wallet)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center
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
                            @endif text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $wallet->name }}</h3>
                            <p class="text-xs text-gray-500">{{ ucfirst($wallet->type) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('wallets.edit', $wallet) }}" class="text-gray-400 hover:text-indigo-600">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" onsubmit="return confirm('Yakin ingin menghapus wallet ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</p>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-wallet text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">Belum ada wallet.</p>
                <a href="{{ route('wallets.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">
                    Buat Wallet Baru
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
