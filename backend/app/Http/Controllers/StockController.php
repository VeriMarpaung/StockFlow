<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\StoreStockInRequest;
use App\Http\Requests\StoreStockOutRequest;
use App\Models\Product;
use App\Services\StockUpdateService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class StockController extends Controller
{
    public function __construct(private readonly StockUpdateService $stockService) {}

    #[OA\Post(
        path: '/api/products/{product}/stock-in',
        tags: ['Stock'],
        summary: 'Tambah stok masuk',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', example: 10),
                    new OA\Property(property: 'note', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Stok berhasil ditambah'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 404, description: 'Produk tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function stockIn(StoreStockInRequest $request, int $product): JsonResponse
    {
        $productModel = Product::findOrFail($product);
        $data         = $request->validated();

        $result = $this->stockService->stockIn(
            $productModel->id,
            $data['quantity'],
            $request->user()->id,
            $data['note'] ?? null
        );

        return response()->json([
            'message'     => 'Stock updated successfully',
            'stock_before' => $result['stock_before'],
            'stock_after' => $result['stock_after'],
        ]);
    }

    #[OA\Post(
        path: '/api/products/{product}/stock-out',
        tags: ['Stock'],
        summary: 'Kurangi stok keluar (optimistic locking)',
        description: 'Butuh version terkini dari produk. Return 409 jika version sudah berubah (race condition terdeteksi).',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity', 'version'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', example: 5),
                    new OA\Property(property: 'version', type: 'integer', example: 3, description: 'Version terkini dari GET /api/products/{id}'),
                    new OA\Property(property: 'note', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Stok berhasil dikurangi'),
            new OA\Response(response: 409, description: 'Version conflict — data diubah user lain, silakan refresh'),
            new OA\Response(response: 422, description: 'Stok tidak mencukupi atau validation error'),
            new OA\Response(response: 404, description: 'Produk tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function stockOut(StoreStockOutRequest $request, int $product): JsonResponse
    {
        $productModel = Product::findOrFail($product);
        $data         = $request->validated();

        $result = $this->stockService->stockOut(
            $productModel->id,
            $data['quantity'],
            $data['version'],
            $request->user()->id,
            $data['note'] ?? null
        );

        if (! $result['success']) {
            if ($result['code'] === 'STOCK_CONFLICT') {
                return response()->json([
                    'message' => 'Data telah berubah, silakan refresh dan coba lagi.',
                    'code'    => 'STOCK_CONFLICT',
                ], 409);
            }

            return response()->json([
                'message' => 'Stok tidak mencukupi.',
                'code'    => 'INSUFFICIENT_STOCK',
            ], 422);
        }

        return response()->json([
            'message'     => 'Stock updated successfully',
            'stock_before' => $result['stock_before'],
            'stock_after' => $result['stock_after'],
        ]);
    }

    #[OA\Post(
        path: '/api/products/{product}/adjust-stock',
        tags: ['Stock'],
        summary: 'Sesuaikan stok ke nilai baru (adjustment)',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', example: 50, description: 'Nilai stok baru (bukan delta)'),
                    new OA\Property(property: 'note', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Stok berhasil disesuaikan'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 404, description: 'Produk tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function adjustStock(AdjustStockRequest $request, int $product): JsonResponse
    {
        $productModel = Product::findOrFail($product);
        $data         = $request->validated();

        $result = $this->stockService->adjustStock(
            $productModel->id,
            $data['quantity'],
            $request->user()->id,
            $data['note'] ?? null
        );

        return response()->json([
            'message'     => 'Stock adjusted successfully',
            'stock_before' => $result['stock_before'],
            'stock_after' => $result['stock_after'],
        ]);
    }

    #[OA\Get(
        path: '/api/products/{product}/transactions',
        tags: ['Stock'],
        summary: 'Riwayat transaksi stok produk',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'List transaksi'),
            new OA\Response(response: 404, description: 'Produk tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function transactions(int $product): JsonResponse
    {
        $productModel = Product::findOrFail($product);

        $transactions = $productModel->stockTransactions()
            ->with('user:id,name')
            ->latest('created_at')
            ->get();

        return response()->json(['data' => $transactions]);
    }
}
