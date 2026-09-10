<?php

namespace App\Repositories;

use App\Models\OrderItem;
use Illuminate\Support\Collection;

class OrderItemRepository
{
    public function opCreate(array $data): OrderItem
    {
        return OrderItem::create($data);
    }

    public function opCreateMany(array $items): Collection
    {
        $created = collect();
        foreach ($items as $item) {
            $created->push(OrderItem::create($item));
        }

        return $created;
    }
}
