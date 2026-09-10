<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        Log::info("OrderObserver: New Order #{$order->id} persisted with status '{$order->status->value}'");
    }

    public function updated(Order $order): void
    {
        Log::info("OrderObserver: Order #{$order->id} updated status to '{$order->status->value}'");
    }
}
