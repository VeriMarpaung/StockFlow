<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepository $repo) {}

    #[OA\Get(
        path: '/api/products',
        tags: ['Products'],
        summary: 'Daftar semua produk (cached Redis 5 menit)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List produk',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'sku', type: 'string'),
                        new OA\Property(property: 'price', type: 'number'),
                        new OA\Property(property: 'stock', type: 'integer'),
                        new OA\Property(property: 'threshold', type: 'integer'),
                        new OA\Property(property: 'version', type: 'integer', description: 'Optimistic locking version'),
                    ]))
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->repo->getAll()]);
    }

    #[OA\Post(
        path: '/api/products',
        tags: ['Products'],
        summary: 'Buat produk baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['category_id', 'name', 'sku', 'price'],
                properties: [
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Wireless Mouse'),
                    new OA\Property(property: 'sku', type: 'string', example: 'ELEC-099'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'number', example: 280000),
                    new OA\Property(property: 'stock', type: 'integer', example: 50),
                    new OA\Property(property: 'threshold', type: 'integer', example: 10),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Produk berhasil dibuat'),
            new OA\Response(response: 422, description: 'Validation error (termasuk duplicate SKU)'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->repo->create($request->validated());

        return response()->json($product, 201);
    }

    #[OA\Get(
        path: '/api/products/{product}',
        tags: ['Products'],
        summary: 'Detail satu produk (include version untuk optimistic locking)',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Data produk'),
            new OA\Response(response: 404, description: 'Produk tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(int $product): JsonResponse
    {
        return response()->json($this->repo->findById($product));
    }

    #[OA\Put(
        path: '/api/products/{product}',
        tags: ['Products'],
        summary: 'Update produk',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'price', type: 'number'),
                new OA\Property(property: 'threshold', type: 'integer'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Produk berhasil diupdate'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 404, description: 'Produk tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function update(UpdateProductRequest $request, int $product): JsonResponse
    {
        $model = $this->repo->findById($product);

        return response()->json($this->repo->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/products/{product}',
        tags: ['Products'],
        summary: 'Hapus produk',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Produk berhasil dihapus'),
            new OA\Response(response: 404, description: 'Produk tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function destroy(int $product): JsonResponse
    {
        $this->repo->delete($this->repo->findById($product));

        return response()->json(['message' => 'Product deleted']);
    }
}
