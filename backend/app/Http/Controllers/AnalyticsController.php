<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService) {}

    #[OA\Get(
        path: '/api/analytics/insights',
        tags: ['Analytics'],
        summary: 'AI-generated inventory insights (cached Redis 1 jam)',
        description: 'Cache hit: langsung return. Cache miss: panggil LLM, simpan ke Redis TTL 1 jam.',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Insight berhasil',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'insights',     type: 'string',  example: 'Produk Mineral Water konsisten low-stock...'),
                    new OA\Property(property: 'generated_at', type: 'string',  format: 'date-time'),
                    new OA\Property(property: 'cached',       type: 'boolean', example: true),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function insights(): JsonResponse
    {
        $cached   = Cache::has('analytics:insights');
        $insights = Cache::remember('analytics:insights', 3600, fn () =>
            $this->analyticsService->generateInsights()
        );

        return response()->json([
            'insights'     => $insights,
            'generated_at' => now()->toIso8601String(),
            'cached'       => $cached,
        ]);
    }

    #[OA\Post(
        path: '/api/analytics/insights/regenerate',
        tags: ['Analytics'],
        summary: 'Force regenerate AI insight (bypass cache)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Insight berhasil di-regenerate',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'insights',     type: 'string'),
                    new OA\Property(property: 'generated_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'cached',       type: 'boolean', example: false),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function regenerate(): JsonResponse
    {
        Cache::forget('analytics:insights');
        $insights = $this->analyticsService->generateInsights();
        Cache::put('analytics:insights', $insights, 3600);

        return response()->json([
            'insights'     => $insights,
            'generated_at' => now()->toIso8601String(),
            'cached'       => false,
        ]);
    }
}
