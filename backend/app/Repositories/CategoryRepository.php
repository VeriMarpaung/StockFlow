<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    private const CACHE_KEY = 'categories:all';
    private const CACHE_TTL = 600;

    public function getAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () =>
            Category::orderBy('name')->get()
        );
    }

    public function findById(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function create(array $data): Category
    {
        $category = Category::create($data);
        Cache::forget(self::CACHE_KEY);
        return $category;
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        Cache::forget(self::CACHE_KEY);
        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
        Cache::forget(self::CACHE_KEY);
    }
}
