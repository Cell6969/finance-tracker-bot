@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
        <a href="{{ route('categories.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">
            <i class="fas fa-plus mr-1"></i> Tambah Kategori
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Income Categories -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-green-600 flex items-center gap-2">
                <i class="fas fa-arrow-down"></i> Pemasukan
            </h2>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">Nama Kategori</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($incomeCategories as $category)
                            <tr>
                                <td class="px-4 py-3 text-gray-900">{{ $category->name }}</td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('categories.edit', $category) }}" class="text-gray-400 hover:text-indigo-600">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
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
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500">Tidak ada kategori pemasukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expense Categories -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-red-600 flex items-center gap-2">
                <i class="fas fa-arrow-up"></i> Pengeluaran
            </h2>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">Nama Kategori</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($expenseCategories as $category)
                            <tr>
                                <td class="px-4 py-3 text-gray-900">{{ $category->name }}</td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('categories.edit', $category) }}" class="text-gray-400 hover:text-indigo-600">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
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
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500">Tidak ada kategori pengeluaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
