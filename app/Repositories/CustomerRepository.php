<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository
{
    public function opFindByEmail(string $email): ?Customer
    {
        return Customer::where('email', $email)->first();
    }

    public function opFindOrCreateByEmail(string $name, string $email): Customer
    {
        return Customer::firstOrCreate(
            ['email' => $email],
            ['name' => $name]
        );
    }

    public function opCreate(array $data): Customer
    {
        return Customer::create($data);
    }

    public function opFindById(int $id): ?Customer
    {
        return Customer::find($id);
    }
}
