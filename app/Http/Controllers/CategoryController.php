<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Service\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request): View
    {
        $guest = $request->user();
        $categories = $this->categoryService->getAllCategories($guest);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $guest = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:income,expense',
        ]);

        $this->categoryService->createCategory($guest, $validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:income,expense',
        ]);

        $this->categoryService->updateCategory($category, $validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->categoryService->deleteCategory($category);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
