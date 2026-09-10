<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function opCreate(array $data): Order
    {
        return Order::create($data);
    }

    public function opFindById(int $id): ?Order
    {
        return Order::with(['customer', 'items.product'])->find($id);
    }

    public function opGetOrderHistoryByEmail(string $email, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['customer', 'items.product'])
            ->whereHas('customer', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function opGetAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Order::with(['customer', 'items.product'])->latest();

        if ($search) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }
}
