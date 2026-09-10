<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmation #{$this->order->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "<h1>Order Confirmation #{$this->order->id}</h1><p>Thank you for your order! Total: \${$this->order->grand_total}</p>",
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
