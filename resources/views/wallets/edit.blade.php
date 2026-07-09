@extends('layouts.app')

@section('title', 'Edit Wallet')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Wallet</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('wallets.update', $wallet) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Wallet</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $wallet->name) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           required>
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Wallet</label>
                    <select name="type" id="type"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                        <option value="">-- Pilih Tipe --</option>
                        @foreach(['bank', 'ewallet', 'cash', 'investment'] as $type)
                            <option value="{{ $type }}" {{ old('type', $wallet->type) === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="balance" class="block text-sm font-medium text-gray-700 mb-1">Saldo Saat Ini</label>
                    <input type="number" name="balance" id="balance" value="{{ old('balance', $wallet->balance) }}" min="0" step="any"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           required>
                    @error('balance')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <a href="{{ route('wallets.index') }}" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-center hover:bg-gray-300 font-medium">Batal</a>
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
