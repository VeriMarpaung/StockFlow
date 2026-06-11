<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StockUpdatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $productId,
        private readonly int $quantity,
        private readonly string $type
    ) {}

    public function handle(): void
    {
        Log::info('job.processed', [
            'job'        => 'StockUpdatedJob',
            'product_id' => $this->productId,
            'type'       => $this->type,
            'quantity'   => $this->quantity,
        ]);
    }
}
