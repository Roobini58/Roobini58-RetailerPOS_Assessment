<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected readonly OrderService $orderService,
        protected readonly ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $searchEmail = $request->get('email');
        $orders = $this->orderService->fetchAllOrders(
            perPage: 15,
            search: $searchEmail
        );

        return view('orders.index', compact('orders', 'searchEmail'));
    }

    public function create(): View
    {
        $products = $this->productService->fetchProducts();
        $lowStockProducts = $this->productService->fetchLowStockProducts();

        return view('orders.create', compact('products', 'lowStockProducts'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $order = $this->orderService->storeOrder(
            customerData: $validated['customer'],
            itemsData: $validated['items']
        );

        return redirect()
            ->route('orders.show', $order->id)
            ->with('success', "Order #{$order->id} created successfully!");
    }

    public function show(int $id): View
    {
        $order = $this->orderService->fetchOrderById($id);

        if (! $order) {
            abort(404, "Order #{$id} not found.");
        }

        return view('orders.show', compact('order'));
    }
}
