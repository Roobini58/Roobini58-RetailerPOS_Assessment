<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Support\Facades\Log;

class LogOrderCreation
{
    public function handle(OrderCreated $event): void
    {
        Log::info("Audit Trail: Order #{$event->order->id} created successfully for Customer #{$event->order->customer_id} with Grand Total {$event->order->grand_total}");
    }
}
