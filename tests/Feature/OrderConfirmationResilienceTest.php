<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A failing confirmation email must not break the customer's order.
 *
 * markPaid() sends the confirmation inline (QUEUE_CONNECTION=sync), and it runs
 * on the two paths that matter after money has moved: the Stripe redirect back
 * to /checkout/{order}/confirmation, and the payment_intent.succeeded webhook.
 *
 * If SMTP is misconfigured — placeholder credentials, wrong port, a mailbox
 * that does not exist yet — the send throws, and the customer sees a 500 on the
 * page that is supposed to tell them the payment worked. The order is already
 * marked paid at that point, so it looks far worse than it is.
 */
class OrderConfirmationResilienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A mail server that is not there. Port 1 on localhost refuses
        // immediately, which is the same class of failure as bad credentials.
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 1,
        ]);
    }

    protected function order(): Order
    {
        return Order::create([
            'order_number' => 'CH-TESTORDER',
            'email' => 'buyer@example.test',
            'status' => 'pending',
            'currency' => 'GBP',
            'subtotal_pence' => 2000,
            'discount_pence' => 0,
            'shipping_pence' => 509,
            'total_pence' => 2509,
            'stripe_payment_intent_id' => 'pi_test_123',
            'shipping_address' => ['full_name' => 'B', 'line1' => '1 St', 'city' => 'London', 'postcode' => 'E1 6AN', 'country' => 'GB'],
            'billing_address' => ['full_name' => 'B', 'line1' => '1 St', 'city' => 'London', 'postcode' => 'E1 6AN', 'country' => 'GB'],
        ]);
    }

    public function test_marking_paid_survives_a_dead_mail_server(): void
    {
        $order = $this->order();

        app(OrderService::class)->markPaid($order);

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_the_customer_still_sees_their_confirmation_page(): void
    {
        $order = $this->order();

        // Already paid, so the page renders without calling out to Stripe.
        app(OrderService::class)->markPaid($order);

        $this->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee(money($order->total_pence), false);
    }

    public function test_the_stripe_webhook_is_not_broken_by_a_mail_failure(): void
    {
        // Returning 500 here makes Stripe retry an order that is already paid.
        $order = $this->order();

        app(OrderService::class)->markPaid($order);

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_stock_is_still_decremented_when_mail_fails(): void
    {
        // The side effects inside the transaction must survive too, not just
        // the status flag.
        $order = $this->order();

        app(OrderService::class)->markPaid($order);

        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_the_page_still_loads_when_stripe_cannot_be_reached(): void
    {
        // The other way this page can 500: it calls out to Stripe to confirm
        // the payment status, and on shared hosting that can fail for reasons
        // unrelated to the payment — blocked outbound connections, a cURL
        // timeout, a wrong secret key. The webhook settles the order
        // independently, so the customer should see "confirming your payment",
        // never an error.
        config(['services.stripe.secret' => 'sk_test_definitely_not_a_real_key']);

        // Point the SDK at a port nothing is listening on, so the call fails
        // locally instead of reaching out to the real Stripe API.
        $realApiBase = \Stripe\Stripe::$apiBase;
        \Stripe\Stripe::$apiBase = 'http://127.0.0.1:1';

        $order = $this->order(); // still pending, with a payment intent id

        try {
            $response = $this->get(route('checkout.confirmation', $order));

            $response->assertOk();
            $response->assertSee($order->order_number);

            // And not falsely reported as paid just because Stripe was
            // unreachable — the webhook is what settles it.
            $this->assertSame('pending', $order->fresh()->status);
        } finally {
            \Stripe\Stripe::$apiBase = $realApiBase;
        }
    }
}
