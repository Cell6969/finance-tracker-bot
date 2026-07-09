@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Transaksi</h1>
        <div class="flex gap-2">
            <a href="{{ route('transactions.income') }}" class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 text-sm font-medium">
                <i class="fas fa-arrow-down mr-1"></i> Pemasukan
            </a>
            <a href="{{ route('transactions.expense') }}" class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 text-sm font-medium">
                <i class="fas fa-arrow-up mr-1"></i> Pengeluaran
            </a>
            <a href="{{ route('transactions.transfer') }}" class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                <i class="fas fa-exchange-alt mr-1"></i> Transfer
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Tipe</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Kategori</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Wallet</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Deskripsi</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Jumlah</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-900 whitespace-nowrap">
                                {{ $transaction->transaction_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($transaction->type === 'income')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <i class="fas fa-arrow-down"></i> Pemasukan
                                    </span>
                                @elseif($transaction->type === 'expense')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        <i class="fas fa-arrow-up"></i> Pengeluaran
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        <i class="fas fa-exchange-alt"></i> Transfer
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $transaction->category?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $transaction->account?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 max-w-[200px] truncate">
                                {{ $transaction->description ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold whitespace-nowrap
                                @if($transaction->type === 'income') text-green-600
                                @elseif($transaction->type === 'expense') text-red-600
                                @else text-blue-600
                                @endif">
                                @if($transaction->type === 'income') +
                                @elseif($transaction->type === 'expense') -
                                @endif
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500">Belum ada transaksi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
