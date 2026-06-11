<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepository $repo) {}

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Daftar semua kategori (cached Redis 10 menit)",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List kategori",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string", nullable=true)
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
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Buat kategori baru",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Electronics"),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Kategori berhasil dibuat"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->repo->create($request->validated());

        return response()->json($category, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{category}",
     *     tags={"Categories"},
     *     summary="Detail satu kategori",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Data kategori"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(int $category): JsonResponse
    {
        return response()->json($this->repo->findById($category));
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{category}",
     *     tags={"Categories"},
     *     summary="Update kategori",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Kategori berhasil diupdate"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(UpdateCategoryRequest $request, int $category): JsonResponse
    {
        $model = $this->repo->findById($category);

        return response()->json($this->repo->update($model, $request->validated()));
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{category}",
     *     tags={"Categories"},
     *     summary="Hapus kategori",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Kategori berhasil dihapus"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(int $category): JsonResponse
    {
        $this->repo->delete($this->repo->findById($category));

        return response()->json(['message' => 'Category deleted']);
    }
}
