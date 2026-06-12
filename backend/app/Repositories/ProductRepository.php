<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    private const CACHE_KEY = 'products:all';
    private const CACHE_TTL = 300;

    public function getAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () =>
            Product::with('category')->orderBy('name')->get()
        );
    }

    public function findById(int $id): Product
    {
        return Product::with('category')->findOrFail($id);
    }

    public function create(array $data): Product
    {
        $product = Product::create($data);
        Cache::forget(self::CACHE_KEY);
        return $product->load('category');
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        Cache::forget(self::CACHE_KEY);
        return $product->fresh('category');
    }

    public function delete(Product $product): void
    {
        $product->delete();
        Cache::forget(self::CACHE_KEY);
    }

    public function getLowStock(): Collection
    {
        return Product::with('category')->lowStock()->get();
    }

    /**
     * Invalidate the product catalog cache.
     *
     * Called after stock mutations (in/out/adjust) which change stock & version
     * via raw queries that bypass the Eloquent events this repository relies on
     * for create/update/delete. Without this, GET /api/products keeps serving a
     * stale snapshot (old stock & version) for up to CACHE_TTL.
     */
    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
