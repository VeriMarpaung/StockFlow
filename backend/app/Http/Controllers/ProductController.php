<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepository $repo) {}

    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Daftar semua produk (cached Redis 5 menit)",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List produk",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="sku", type="string"),
     *                     @OA\Property(property="price", type="number"),
     *                     @OA\Property(property="stock", type="integer"),
     *                     @OA\Property(property="threshold", type="integer"),
     *                     @OA\Property(property="version", type="integer"),
     *                     @OA\Property(property="category", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->repo->getAll()]);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Buat produk baru",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id","name","sku","price"},
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Wireless Mouse"),
     *             @OA\Property(property="sku", type="string", example="ELEC-099"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="price", type="number", example=280000),
     *             @OA\Property(property="stock", type="integer", example=50),
     *             @OA\Property(property="threshold", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Produk berhasil dibuat"),
     *     @OA\Response(response=422, description="Validation error (termasuk duplicate SKU)"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->repo->create($request->validated());

        return response()->json($product, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Detail satu produk",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Data produk beserta version untuk optimistic locking"),
     *     @OA\Response(response=404, description="Produk tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(int $product): JsonResponse
    {
        return response()->json($this->repo->findById($product));
    }

    /**
     * @OA\Put(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Update produk",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="threshold", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Produk berhasil diupdate"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Produk tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(UpdateProductRequest $request, int $product): JsonResponse
    {
        $model = $this->repo->findById($product);

        return response()->json($this->repo->update($model, $request->validated()));
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Hapus produk",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Produk berhasil dihapus"),
     *     @OA\Response(response=404, description="Produk tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(int $product): JsonResponse
    {
        $this->repo->delete($this->repo->findById($product));

        return response()->json(['message' => 'Product deleted']);
    }
}
