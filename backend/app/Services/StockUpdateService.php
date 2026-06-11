<?php

namespace App\Services;

use App\Jobs\StockUpdatedJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockUpdateService
{
    public function stockIn(int $productId, int $quantity, int $userId, ?string $note = null): array
    {
        return DB::transaction(function () use ($productId, $quantity, $userId, $note) {
            $product = DB::table('products')->where('id', $productId)->lockForUpdate()->first();

            $stockBefore = $product->stock;
            $stockAfter  = $stockBefore + $quantity;

            DB::table('products')->where('id', $productId)->update([
                'stock'      => $stockAfter,
                'updated_at' => now(),
            ]);

            DB::table('stock_transactions')->insert([
                'product_id'  => $productId,
                'user_id'     => $userId,
                'type'        => 'in',
                'quantity'    => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'note'        => $note,
                'created_at'  => now(),
            ]);

            Log::info('stock.updated', [
                'product_id'  => $productId,
                'type'        => 'in',
                'quantity'    => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'user_id'     => $userId,
            ]);

            StockUpdatedJob::dispatch($productId, $quantity, 'in');

            return ['success' => true, 'stock_before' => $stockBefore, 'stock_after' => $stockAfter];
        });
    }

    public function stockOut(int $productId, int $quantity, int $version, int $userId, ?string $note = null): array
    {
        return DB::transaction(function () use ($productId, $quantity, $version, $userId, $note) {
            $product = DB::table('products')->where('id', $productId)->first();

            if ($product->stock < $quantity) {
                return ['success' => false, 'code' => 'INSUFFICIENT_STOCK'];
            }

            $stockBefore = $product->stock;

            $affected = DB::table('products')
                ->where('id', $productId)
                ->where('version', $version)
                ->where('stock', '>=', $quantity)
                ->update([
                    'stock'      => DB::raw('stock - ' . (int) $quantity),
                    'version'    => DB::raw('version + 1'),
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                Log::warning('stock.conflict', [
                    'product_id'        => $productId,
                    'requested_version' => $version,
                    'quantity'          => $quantity,
                    'user_id'           => $userId,
                ]);

                return ['success' => false, 'code' => 'STOCK_CONFLICT'];
            }

            $stockAfter = $stockBefore - $quantity;

            DB::table('stock_transactions')->insert([
                'product_id'  => $productId,
                'user_id'     => $userId,
                'type'        => 'out',
                'quantity'    => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'note'        => $note,
                'created_at'  => now(),
            ]);

            Log::info('stock.updated', [
                'product_id'  => $productId,
                'type'        => 'out',
                'quantity'    => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'user_id'     => $userId,
            ]);

            StockUpdatedJob::dispatch($productId, $quantity, 'out');

            return ['success' => true, 'stock_before' => $stockBefore, 'stock_after' => $stockAfter];
        });
    }

    public function adjustStock(int $productId, int $newQuantity, int $userId, ?string $note = null): array
    {
        return DB::transaction(function () use ($productId, $newQuantity, $userId, $note) {
            $product = DB::table('products')->where('id', $productId)->lockForUpdate()->first();

            $stockBefore = $product->stock;
            $delta       = abs($newQuantity - $stockBefore);

            DB::table('products')->where('id', $productId)->update([
                'stock'      => $newQuantity,
                'updated_at' => now(),
            ]);

            DB::table('stock_transactions')->insert([
                'product_id'  => $productId,
                'user_id'     => $userId,
                'type'        => 'adjustment',
                'quantity'    => $delta,
                'stock_before' => $stockBefore,
                'stock_after' => $newQuantity,
                'note'        => $note,
                'created_at'  => now(),
            ]);

            Log::info('stock.updated', [
                'product_id'  => $productId,
                'type'        => 'adjustment',
                'quantity'    => $delta,
                'stock_before' => $stockBefore,
                'stock_after' => $newQuantity,
                'user_id'     => $userId,
            ]);

            StockUpdatedJob::dispatch($productId, $delta, 'adjustment');

            return ['success' => true, 'stock_before' => $stockBefore, 'stock_after' => $newQuantity];
        });
    }
}
