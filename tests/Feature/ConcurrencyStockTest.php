<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ConcurrencyStockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test atomic conditional decrement logic at the repository level.
     * Simulates two competing concurrent workers attempting to claim the final item in stock.
     */
    public function test_atomic_stock_decrement_prevents_overselling_on_last_unit(): void
    {
        /** @var ProductRepository $productRepo */
        $productRepo = app(ProductRepository::class);

        $product = Product::factory()->create([
            'name' => 'Limited Edition Item',
            'code' => 'LIMITED-001',
            'stock_on_hand' => 1, // Exactly 1 unit remaining
        ]);

        // Simulated Worker 1 executes deduction
        $worker1Success = $productRepo->opDecrementStockForUpdate($product->id, 1);

        // Simulated Worker 2 immediately attempts deduction on the same product
        $worker2Success = $productRepo->opDecrementStockForUpdate($product->id, 1);

        $this->assertTrue($worker1Success, 'First worker must successfully claim the stock unit.');
        $this->assertFalse($worker2Success, 'Second worker must be rejected because stock was depleted by Worker 1.');

        $freshProduct = $productRepo->opFindById($product->id);
        $this->assertEquals(0, $freshProduct->stock_on_hand, 'Stock on hand must be exactly 0, never negative.');
    }

    /**
     * Test end-to-end OrderService execution under simulated race condition.
     */
    public function test_order_service_rejects_second_concurrent_buyer_when_stock_reaches_zero(): void
    {
        Queue::fake();
        Event::fake();

        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);

        $product = Product::factory()->create([
            'name' => 'Hot Ticket Item',
            'code' => 'TICKET-999',
            'price_per_unit' => 50.00,
            'tax_percentage' => 0.00,
            'stock_on_hand' => 1,
        ]);

        $customer1Data = ['name' => 'Buyer One', 'email' => 'buyer1@example.com'];
        $customer2Data = ['name' => 'Buyer Two', 'email' => 'buyer2@example.com'];
        $itemsData = [['product_id' => $product->id, 'quantity' => 1]];

        // Buyer 1 places order successfully
        $order1 = $orderService->storeOrder($customer1Data, $itemsData);
        $this->assertNotNull($order1);

        // Buyer 2 attempts placing order on now-empty stock -> must throw InsufficientStockException
        $this->expectException(InsufficientStockException::class);
        $orderService->storeOrder($customer2Data, $itemsData);
    }
}
