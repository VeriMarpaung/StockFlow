<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    public function generateInsights(): string
    {
        $data   = $this->aggregateInventoryData();
        $prompt = $this->buildPrompt($data);
        return $this->callLlm($prompt);
    }

    private function aggregateInventoryData(): array
    {
        $lowStockList = Product::lowStock()
            ->get(['name', 'stock', 'threshold'])
            ->map(fn ($p) => "{$p->name} (stok: {$p->stock}, threshold: {$p->threshold})")
            ->implode('; ');

        $topOutgoing = StockTransaction::with('product')
            ->where('type', 'out')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->map(fn ($t) => ($t->product?->name ?? 'Unknown') . " ({$t->total_qty} unit)")
            ->implode('; ');

        return [
            'total_products'     => Product::count(),
            'low_stock_count'    => Product::lowStock()->count(),
            'low_stock_list'     => $lowStockList ?: 'Tidak ada',
            'top_outgoing'       => $topOutgoing ?: 'Belum ada transaksi minggu ini',
            'transactions_today' => StockTransaction::whereDate('created_at', today())->count(),
        ];
    }

    private function buildPrompt(array $data): string
    {
        return <<<PROMPT
Anda adalah analis sistem inventori. Berdasarkan data berikut, berikan 3-4 insight actionable dalam Bahasa Indonesia:

Data Inventori Saat Ini:
- Total produk: {$data['total_products']}
- Produk stok rendah: {$data['low_stock_count']} produk
- Produk kritis: {$data['low_stock_list']}
- Top 5 transaksi keluar minggu ini: {$data['top_outgoing']}
- Transaksi hari ini: {$data['transactions_today']} transaksi

Berikan insight tentang:
1. Produk paling mendesak untuk direstock
2. Pola permintaan dari data transaksi
3. Rekomendasi tindakan konkret

Jawab dalam 150-200 kata, ringkas dan langsung ke poin.
PROMPT;
    }

    private function callLlm(string $prompt): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai.key'),
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post(config('services.ai.url'), [
                'model'      => config('services.ai.model'),
                'messages'   => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => 600,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                Log::info('analytics.insight_generated', ['chars' => strlen((string) $content)]);
                return $content ?? 'Gagal memuat insight dari AI.';
            }

            Log::warning('analytics.llm_error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return 'Layanan AI sedang tidak tersedia. Coba lagi beberapa saat.';
        } catch (\Exception $e) {
            Log::error('analytics.llm_exception', ['error' => $e->getMessage()]);
            return 'Gagal menghubungi layanan AI.';
        }
    }
}
