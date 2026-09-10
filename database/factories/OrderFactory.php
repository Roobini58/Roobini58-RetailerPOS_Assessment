<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'subtotal' => 100.00,
            'tax_amount' => 10.00,
            'grand_total' => 110.00,
            'status' => OrderStatus::CONFIRMED,
        ];
    }
}
