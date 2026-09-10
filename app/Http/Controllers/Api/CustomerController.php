<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Services\CustomerService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(
        protected readonly CustomerService $customerService,
        protected readonly OrderService $orderService
    ) {}

    public function show(string $email): JsonResponse|CustomerResource
    {
        $customer = $this->customerService->fetchCustomerByEmail($email);

        if (! $customer) {
            return response()->json([
                'message' => "Customer with email '{$email}' not found.",
                'errors' => ['email' => ["Customer '{$email}' does not exist."]],
                'code' => 404,
            ], 404);
        }

        return new CustomerResource($customer);
    }

    public function orders(string $email): AnonymousResourceCollection
    {
        $orders = $this->orderService->fetchCustomerOrderHistory($email);

        return OrderResource::collection($orders);
    }
}
