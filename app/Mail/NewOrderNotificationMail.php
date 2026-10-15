<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the shop owner a paid order has come in.
 *
 * Sent alongside the customer's confirmation, so it fires once per order, on
 * payment rather than on checkout — an unpaid order is an abandoned basket and
 * is not worth an inbox notification.
 */
class NewOrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function build(): self
    {
        // The subject carries the number and the value so it is useful in a
        // notification preview, without opening anything.
        return $this
            ->subject("New order #{$this->order->order_number} — ".money($this->order->total_pence))
            // Replying goes straight to the customer, which is nearly always
            // what you want from this email.
            ->replyTo($this->order->email)
            ->view('emails.new-order-notification');
    }
}
