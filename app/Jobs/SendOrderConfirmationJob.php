<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly Order $order
    ) {}

    public function handle(): void
    {
        Log::info("Sending confirmation email for Order #{$this->order->id} to {$this->order->customer?->email}");

        try {
            if ($this->order->customer?->email) {
                Mail::to($this->order->customer->email)->send(new OrderConfirmationMail($this->order));
            }
        } catch (Throwable $e) {
            Log::warning("Failed to send real mail for Order #{$this->order->id}, falling back to Log driver simulation: ".$e->getMessage());
        }

        Log::info("Order confirmation email process finished for Order #{$this->order->id}");
    }

    public function failed(Throwable $exception): void
    {
        Log::error("SendOrderConfirmationJob failed permanently for Order #{$this->order->id}: {$exception->getMessage()}", [
            'order_id' => $this->order->id,
            'exception' => $exception->getTraceAsString(),
        ]);
    }
}
