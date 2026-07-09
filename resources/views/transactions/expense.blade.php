@extends('layouts.app')

@section('title', 'Catat Pengeluaran')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Catat Pengeluaran</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('transactions.storeExpense') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="account_id" class="block text-sm font-medium text-gray-700 mb-1">Wallet</label>
                    <select name="account_id" id="account_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                        <option value="">-- Pilih Wallet --</option>
                        @foreach($wallets as $wallet)
                            <option value="{{ $wallet->id }}" {{ old('account_id') == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->name }} (Rp {{ number_format($wallet->balance, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('account_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="category_id" id="category_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                        <input type="number" name="amount" id="amount" value="{{ old('amount') }}" min="0.01" step="any"
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required>
                    </div>
                    @error('amount')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="transaction_at" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="transaction_at" id="transaction_at" value="{{ old('transaction_at', date('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           required>
                    @error('transaction_at')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <a href="{{ route('transactions.index') }}" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-center hover:bg-gray-300 font-medium">Batal</a>
                <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-medium">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>
@endsection
