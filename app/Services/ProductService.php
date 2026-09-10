<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(
        protected readonly ProductRepository $productRepository
    ) {}

    public function fetchLowStockProducts(?int $threshold = null): Collection
    {
        $effectiveThreshold = $threshold ?? (int) config('inventory.low_stock_threshold', 5);

        return $this->productRepository->opGetLowStockProducts($effectiveThreshold);
    }

    public function fetchProducts(): Collection
    {
        return $this->productRepository->opGetAll();
    }
}
