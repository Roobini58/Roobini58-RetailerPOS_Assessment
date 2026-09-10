<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $sampleProducts = [
            [
                'name' => 'Wireless Mechanical Keyboard',
                'code' => 'SKU-KB1001',
                'price_per_unit' => 89.99,
                'tax_percentage' => 10.00,
                'stock_on_hand' => 25,
            ],
            [
                'name' => 'Ergonomic Gaming Mouse',
                'code' => 'SKU-MS2002',
                'price_per_unit' => 49.50,
                'tax_percentage' => 10.00,
                'stock_on_hand' => 4, // Low stock sample
            ],
            [
                'name' => 'UltraWide 34" Monitor',
                'code' => 'SKU-MN3003',
                'price_per_unit' => 449.00,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 12,
            ],
            [
                'name' => 'USB-C Multi-Port Hub',
                'code' => 'SKU-HB4004',
                'price_per_unit' => 29.99,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 2, // Low stock sample
            ],
            [
                'name' => 'Noise Cancelling Headphones',
                'code' => 'SKU-HP5005',
                'price_per_unit' => 199.99,
                'tax_percentage' => 15.00,
                'stock_on_hand' => 18,
            ],
            [
                'name' => 'Desk Mat Extended (Black)',
                'code' => 'SKU-DM6006',
                'price_per_unit' => 19.99,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 1, // Critical low stock sample
            ],
        ];

        foreach ($sampleProducts as $productData) {
            Product::updateOrCreate(['code' => $productData['code']], $productData);
        }
    }
}
