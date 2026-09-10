<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmationJob;
use App\Models\Order;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected readonly OrderRepository $orderRepository,
        protected readonly OrderItemRepository $orderItemRepository,
        protected readonly ProductRepository $productRepository,
        protected readonly CustomerRepository $customerRepository
    ) {}

    /**
     * Store a new order, deducting stock safely within a DB transaction.
     *
     * @throws InsufficientStockException
     */
    public function storeOrder(array $customerData, array $itemsData): Order
    {
        $order = DB::transaction(function () use ($customerData, $itemsData) {
            // 1. Find or Create Customer
            $customer = $this->customerRepository->opFindOrCreateByEmail(
                $customerData['name'],
                $customerData['email']
            );

            $stockErrors = [];
            $preparedItems = [];
            $subtotal = 0.00;
            $taxAmount = 0.00;

            $roundingMode = config('inventory.tax_rounding_mode', PHP_ROUND_HALF_UP);

            // 2. Validate Stock & Deduct Pessimistically
            foreach ($itemsData as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];

                // Pessimistically lock row for read/update
                $product = $this->productRepository->opFindByIdForUpdate($productId);

                if (! $product) {
                    $stockErrors["items.{$productId}"] = ["Product with ID {$productId} does not exist."];

                    continue;
                }

                if ($product->stock_on_hand < $quantity) {
                    $stockErrors["items.{$productId}"] = [
                        "Insufficient stock for product '{$product->name}'. Available: {$product->stock_on_hand}, Requested: {$quantity}",
                    ];

                    continue;
                }

                // Atomic decrement
                $deducted = $this->productRepository->opDecrementStockForUpdate($productId, $quantity);
                if (! $deducted) {
                    $stockErrors["items.{$productId}"] = [
                        "Concurrency lock conflict during stock deduction for product '{$product->name}'.",
                    ];

                    continue;
                }

                // Compute item pricing
                $unitPrice = (float) $product->price_per_unit;
                $lineSubtotal = round($unitPrice * $quantity, 2, $roundingMode);
                $lineTax = round($lineSubtotal * ((float) $product->tax_percentage / 100), 2, $roundingMode);
                $lineTotal = round($lineSubtotal + $lineTax, 2, $roundingMode);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            if (! empty($stockErrors)) {
                Log::warning('Order creation rejected due to stock issues', ['errors' => $stockErrors]);
                throw new InsufficientStockException('Order validation failed due to stock availability.', $stockErrors);
            }

            $grandTotal = round($subtotal + $taxAmount, 2, $roundingMode);

            // 3. Persist Order Header
            $order = $this->orderRepository->opCreate([
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'status' => OrderStatus::CONFIRMED,
            ]);

            // 4. Persist Order Line Items
            $formattedItems = array_map(function ($item) use ($order) {
                $item['order_id'] = $order->id;

                return $item;
            }, $preparedItems);

            $this->orderItemRepository->opCreateMany($formattedItems);

            return $order;
        });

        // 5. Post-Commit Actions (Job & Event)
        DB::afterCommit(function () use ($order) {
            $freshOrder = $this->orderRepository->opFindById($order->id);
            SendOrderConfirmationJob::dispatch($freshOrder)->onQueue(config('inventory.queue', 'default'));
            event(new OrderCreated($freshOrder));
        });

        return $this->orderRepository->opFindById($order->id);
    }

    public function fetchCustomerOrderHistory(string $email, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->opGetOrderHistoryByEmail($email, $perPage);
    }

    public function fetchAllOrders(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->orderRepository->opGetAllPaginated($perPage, $search);
    }

    public function fetchOrderById(int $id): ?Order
    {
        return $this->orderRepository->opFindById($id);
    }
}
