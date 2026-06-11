<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepository $repo) {}

    #[OA\Get(
        path: '/api/categories',
        tags: ['Categories'],
        summary: 'Daftar semua kategori (cached Redis 10 menit)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List kategori',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
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
        path: '/api/categories',
        tags: ['Categories'],
        summary: 'Buat kategori baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Kategori berhasil dibuat'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->repo->create($request->validated());

        return response()->json($category, 201);
    }

    #[OA\Get(
        path: '/api/categories/{category}',
        tags: ['Categories'],
        summary: 'Detail satu kategori',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Data kategori'),
            new OA\Response(response: 404, description: 'Kategori tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(int $category): JsonResponse
    {
        return response()->json($this->repo->findById($category));
    }

    #[OA\Put(
        path: '/api/categories/{category}',
        tags: ['Categories'],
        summary: 'Update kategori',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Kategori berhasil diupdate'),
            new OA\Response(response: 404, description: 'Kategori tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function update(UpdateCategoryRequest $request, int $category): JsonResponse
    {
        $model = $this->repo->findById($category);

        return response()->json($this->repo->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/categories/{category}',
        tags: ['Categories'],
        summary: 'Hapus kategori',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Kategori berhasil dihapus'),
            new OA\Response(response: 404, description: 'Kategori tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function destroy(int $category): JsonResponse
    {
        $this->repo->delete($this->repo->findById($category));

        return response()->json(['message' => 'Category deleted']);
    }
}
