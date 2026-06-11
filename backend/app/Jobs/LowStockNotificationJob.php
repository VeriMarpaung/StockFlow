<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LowStockNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $productId) {}

    public function handle(): void
    {
        $product = Product::find($this->productId);

        if (! $product || $product->stock > $product->threshold) {
            return;
        }

        // Avoid duplicate unread notifications for the same product
        $exists = Notification::where('type', 'low_stock')
            ->where('data->product_id', $this->productId)
            ->whereNull('read_at')
            ->exists();

        if ($exists) {
            return;
        }

        Notification::create([
            'user_id' => null, // broadcast: visible to all admins
            'type'    => 'low_stock',
            'title'   => "Stok Rendah: {$product->name}",
            'message' => "Stok {$product->name} saat ini {$product->stock} unit, di bawah threshold {$product->threshold} unit.",
            'data'    => [
                'product_id' => $this->productId,
                'stock'      => $product->stock,
                'threshold'  => $product->threshold,
            ],
        ]);

        Log::info('job.processed', [
            'job'        => 'LowStockNotificationJob',
            'product_id' => $this->productId,
            'stock'      => $product->stock,
            'threshold'  => $product->threshold,
        ]);
    }
}
