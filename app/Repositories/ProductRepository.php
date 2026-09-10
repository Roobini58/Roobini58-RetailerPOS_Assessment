<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function opFindById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function opFindByIds(array $ids): Collection
    {
        return Product::whereIn('id', $ids)->get();
    }

    public function opFindByIdForUpdate(int $id): ?Product
    {
        return Product::where('id', $id)
            ->lockForUpdate()
            ->first();
    }

    public function opGetLowStockProducts(int $threshold): Collection
    {
        return Product::lowStock($threshold)->get();
    }

    public function opGetAll(): Collection
    {
        return Product::orderBy('name', 'asc')->get();
    }

    public function opDecrementStockForUpdate(int $productId, int $quantity): bool
    {
        $affected = DB::table('products')
            ->where('id', $productId)
            ->where('stock_on_hand', '>=', $quantity)
            ->decrement('stock_on_hand', $quantity);

        return $affected > 0;
    }

    public function opCreate(array $data): Product
    {
        return Product::create($data);
    }

    public function opUpdate(int $id, array $data): bool
    {
        $product = Product::find($id);

        return $product ? $product->update($data) : false;
    }
}
