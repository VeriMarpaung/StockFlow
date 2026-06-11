<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepository $repo) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->repo->getAll()]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->repo->create($request->validated());

        return response()->json($product, 201);
    }

    public function show(int $product): JsonResponse
    {
        return response()->json($this->repo->findById($product));
    }

    public function update(UpdateProductRequest $request, int $product): JsonResponse
    {
        $model = $this->repo->findById($product);

        return response()->json($this->repo->update($model, $request->validated()));
    }

    public function destroy(int $product): JsonResponse
    {
        $this->repo->delete($this->repo->findById($product));

        return response()->json(['message' => 'Product deleted']);
    }
}
