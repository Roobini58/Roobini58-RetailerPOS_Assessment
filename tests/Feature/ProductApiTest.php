<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_low_stock_products_with_custom_threshold(): void
    {
        Product::factory()->create(['name' => 'High Stock Item', 'stock_on_hand' => 50]);
        Product::factory()->create(['name' => 'Low Stock Item A', 'stock_on_hand' => 3]);
        Product::factory()->create(['name' => 'Low Stock Item B', 'stock_on_hand' => 5]);
        Product::factory()->create(['name' => 'Out of Stock Item', 'stock_on_hand' => 0]);

        // Default threshold in config is 5
        $response = $this->getJson('/api/product/low-stock?threshold=5');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // 3, 5, 0 are <= 5

        // Custom threshold = 2
        $response2 = $this->getJson('/api/product/low-stock?threshold=2');

        $response2->assertStatus(200)
            ->assertJsonCount(1, 'data'); // Only 0 is <= 2
    }

    public function test_can_fetch_all_products(): void
    {
        Product::factory()->count(4)->create();

        $response = $this->getJson('/api/product');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }
}
