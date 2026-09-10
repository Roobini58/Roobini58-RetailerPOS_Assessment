<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetLowStockRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        protected readonly ProductService $productService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $products = $this->productService->fetchProducts();

        return ProductResource::collection($products);
    }

    public function lowStock(GetLowStockRequest $request): AnonymousResourceCollection
    {
        $threshold = $request->has('threshold') ? (int) $request->input('threshold') : null;
        $products = $this->productService->fetchLowStockProducts($threshold);

        return ProductResource::collection($products);
    }
}
