<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/analytics/insights",
     *     tags={"Analytics"},
     *     summary="AI-generated inventory insights (cached Redis 1 jam)",
     *     description="Mengambil insight dari LLM berdasarkan data inventori teragregasi. Cache hit langsung return. Cache miss: panggil LLM, simpan ke Redis TTL 1 jam.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Insight berhasil digenerate",
     *         @OA\JsonContent(
     *             @OA\Property(property="insights", type="string", example="Produk 'Mineral Water' konsisten low-stock 3 hari berturut-turut..."),
     *             @OA\Property(property="generated_at", type="string", format="datetime"),
     *             @OA\Property(property="cached", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=503, description="LLM service unavailable")
     * )
     */
    public function insights(): JsonResponse
    {
        return response()->json(['message' => 'AI insights not yet implemented'], 501);
    }

    /**
     * @OA\Post(
     *     path="/api/analytics/insights/regenerate",
     *     tags={"Analytics"},
     *     summary="Force regenerate AI insight (bypass cache)",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Insight berhasil di-regenerate"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function regenerate(): JsonResponse
    {
        return response()->json(['message' => 'Regenerate not yet implemented'], 501);
    }
}
