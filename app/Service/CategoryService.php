<?php

namespace App\Service;

use App\Models\Category;
use App\Models\Guest;

class CategoryService
{
    public function createCategory(Guest $guest, array $data): Category
    {
        return Category::create([
            'guest_id' => $guest->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'icon' => $data['icon'] ?? null,
        ]);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function deleteCategory(Category $category): bool
    {
        return $category->delete();
    }

    public function getCategoriesByType(Guest $guest, string $type)
    {
        return $guest->categories()->where('type', $type)->get();
    }

    public function getAllCategories(Guest $guest)
    {
        return $guest->categories()->get();
    }
}
