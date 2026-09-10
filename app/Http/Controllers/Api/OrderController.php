<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        protected readonly OrderService $orderService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->fetchAllOrders(
            perPage: (int) $request->get('per_page', 15),
            search: $request->get('search')
        );

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $order = $this->orderService->storeOrder(
            customerData: $validated['customer'],
            itemsData: $validated['items']
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse|OrderResource
    {
        $order = $this->orderService->fetchOrderById($id);

        if (! $order) {
            return response()->json([
                'message' => "Order #{$id} not found.",
                'errors' => ['id' => ["Order #{$id} does not exist."]],
                'code' => 404,
            ], 404);
        }

        return new OrderResource($order);
    }
}
