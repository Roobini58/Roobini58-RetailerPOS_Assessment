<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_customer_order_history_by_email(): void
    {
        $customer = Customer::factory()->create(['email' => 'alice@example.com']);
        $product = Product::factory()->create();

        Order::factory()->count(3)->create(['customer_id' => $customer->id]);

        $response = $this->getJson('/api/customer/alice@example.com/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_lookup_customer_by_email(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
        ]);

        $response = $this->getJson('/api/customer/alice@example.com');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                    'name' => 'Alice Johnson',
                    'email' => 'alice@example.com',
                ],
            ]);
    }
}
