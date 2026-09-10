<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Jobs\SendOrderConfirmationJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_successfully_and_deduct_stock(): void
    {
        Queue::fake();
        Event::fake();

        $product1 = Product::factory()->create([
            'name' => 'Widget A',
            'code' => 'SKU-001',
            'price_per_unit' => 10.00,
            'tax_percentage' => 10.00,
            'stock_on_hand' => 20,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Widget B',
            'code' => 'SKU-002',
            'price_per_unit' => 50.00,
            'tax_percentage' => 5.00,
            'stock_on_hand' => 10,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ],
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2], // Sub: 20, Tax: 2 -> Line: 22
                ['product_id' => $product2->id, 'quantity' => 1], // Sub: 50, Tax: 2.50 -> Line: 52.50
            ],
        ];

        $response = $this->postJson('/api/order', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer_id',
                    'subtotal',
                    'tax_amount',
                    'grand_total',
                    'status',
                    'customer' => ['id', 'name', 'email'],
                    'items' => [
                        '*' => ['id', 'product_id', 'quantity', 'unit_price', 'line_total'],
                    ],
                ],
            ]);

        // Verify stock deducted
        $this->assertDatabaseHas('products', [
            'id' => $product1->id,
            'stock_on_hand' => 18,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product2->id,
            'stock_on_hand' => 9,
        ]);

        // Verify Customer created
        $this->assertDatabaseHas('customers', [
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
        ]);

        // Verify Order persisted
        $this->assertDatabaseHas('orders', [
            'subtotal' => 70.00,
            'tax_amount' => 4.50,
            'grand_total' => 74.50,
            'status' => 'confirmed',
        ]);

        Queue::assertPushed(SendOrderConfirmationJob::class);
        Event::assertDispatched(OrderCreated::class);
    }

    public function test_order_creation_fails_when_stock_is_insufficient(): void
    {
        Queue::fake();
        Event::fake();

        $product = Product::factory()->create([
            'name' => 'Rare Item',
            'code' => 'RARE-001',
            'price_per_unit' => 100.00,
            'tax_percentage' => 0.00,
            'stock_on_hand' => 1,
        ]);

        $payload = [
            'customer' => [
                'name' => 'John Buyer',
                'email' => 'john@example.com',
            ],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5], // Exceeds available stock (1)
            ],
        ];

        $response = $this->postJson('/api/order', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors', 'code']);

        // Stock must remain unchanged
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 1,
        ]);

        // No order should be written
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);

        Queue::assertNothingPushed();
        Event::assertNotDispatched(OrderCreated::class);
    }

    public function test_validation_fails_for_invalid_order_payload(): void
    {
        $response = $this->postJson('/api/order', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer', 'items']);
    }
}
