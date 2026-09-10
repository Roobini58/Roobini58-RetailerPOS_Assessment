<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;

class CustomerService
{
    public function __construct(
        protected readonly CustomerRepository $customerRepository
    ) {}

    public function fetchCustomerByEmail(string $email): ?Customer
    {
        return $this->customerRepository->opFindByEmail($email);
    }

    public function storeCustomer(string $name, string $email): Customer
    {
        return $this->customerRepository->opFindOrCreateByEmail($name, $email);
    }
}
