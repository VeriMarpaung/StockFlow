<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Contoh Job untuk event-driven architecture.
 *
 * CARA PAKAI:
 *   ProcessDataJob::dispatch($payload);
 *
 * Job ini akan masuk Redis queue dan diproses oleh inaai_worker.
 * Ganti logika di handle() sesuai kebutuhan topik kompetisi.
 */
class ProcessDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        private readonly array $payload
    ) {}

    public function handle(): void
    {
        Log::info('ProcessDataJob started', ['payload' => $this->payload]);

        // TODO: Implementasi logika sesuai topik
        // Contoh: proses file upload, kirim notifikasi, update agregat data, dll.

        Log::info('ProcessDataJob completed', ['payload' => $this->payload]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessDataJob failed', [
            'payload' => $this->payload,
            'error'   => $exception->getMessage(),
        ]);
    }
}
