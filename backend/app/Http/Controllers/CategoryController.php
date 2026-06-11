<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepository $repo) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->repo->getAll()]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->repo->create($request->validated());

        return response()->json($category, 201);
    }

    public function show(int $category): JsonResponse
    {
        return response()->json($this->repo->findById($category));
    }

    public function update(UpdateCategoryRequest $request, int $category): JsonResponse
    {
        $model = $this->repo->findById($category);

        return response()->json($this->repo->update($model, $request->validated()));
    }

    public function destroy(int $category): JsonResponse
    {
        $this->repo->delete($this->repo->findById($category));

        return response()->json(['message' => 'Category deleted']);
    }
}
