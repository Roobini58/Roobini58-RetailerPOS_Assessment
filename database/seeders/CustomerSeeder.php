<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $sampleCustomers = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
            ['name' => 'Bob Smith', 'email' => 'bob.smith@example.com'],
            ['name' => 'Carol Williams', 'email' => 'carol.w@example.com'],
        ];

        foreach ($sampleCustomers as $customerData) {
            Customer::updateOrCreate(['email' => $customerData['email']], $customerData);
        }
    }
}
