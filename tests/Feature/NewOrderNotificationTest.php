<?php

namespace Tests\Feature;

use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The shop owner gets an email when an order is paid for.
 *
 * Sent on payment rather than on checkout: an order row exists before payment,
 * so notifying on creation would mean an inbox full of abandoned baskets.
 */
class NewOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('order_notification_email', 'owner@example.test');
    }

    protected function order(array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'CH-NOTIFY1',
            'email' => 'buyer@example.test',
            'phone' => '07700 900123',
            'status' => 'pending',
            'currency' => 'GBP',
            'subtotal_pence' => 4000,
            'discount_pence' => 0,
            'shipping_pence' => 509,
            'shipping_method' => '1st-class',
            'shipping_method_name' => '1st Class Service',
            'total_pence' => 4509,
            'shipping_address' => [
                'full_name' => 'Ada Buyer',
                'line1' => '12 Test Street',
                'city' => 'Coventry',
                'postcode' => 'CV1 2AB',
                'country' => 'GB',
            ],
            'billing_address' => ['full_name' => 'Ada Buyer', 'line1' => '12 Test Street', 'city' => 'Coventry', 'postcode' => 'CV1 2AB', 'country' => 'GB'],
        ], $overrides));

        $order->items()->create([
            'product_name' => 'Vitamin C Serum',
            'variant_name' => null,
            'sku' => 'VITC-30',
            'unit_price_pence' => 2000,
            'quantity' => 2,
            'line_total_pence' => 4000,
        ]);

        return $order;
    }

    public function test_the_owner_is_emailed_when_an_order_is_paid(): void
    {
        Mail::fake();

        $order = $this->order();
        app(OrderService::class)->markPaid($order);

        Mail::assertSent(NewOrderNotificationMail::class, function ($mail) use ($order) {
            return $mail->hasTo('owner@example.test')
                && $mail->order->is($order);
        });
    }

    public function test_the_customer_confirmation_is_still_sent_too(): void
    {
        Mail::fake();

        $order = $this->order();
        app(OrderService::class)->markPaid($order);

        Mail::assertSent(OrderConfirmationMail::class, fn ($mail) => $mail->hasTo('buyer@example.test'));
        Mail::assertSent(NewOrderNotificationMail::class);
    }

    public function test_nothing_is_sent_to_the_owner_before_payment(): void
    {
        Mail::fake();

        $this->order(); // created, never paid

        Mail::assertNothingSent();
    }

    public function test_it_is_sent_once_even_if_mark_paid_runs_twice(): void
    {
        // The confirmation page and the Stripe webhook both call markPaid, so
        // it routinely runs twice for the same order.
        Mail::fake();

        $order = $this->order();
        $service = app(OrderService::class);

        $service->markPaid($order);
        $service->markPaid($order->fresh());

        Mail::assertSent(NewOrderNotificationMail::class, 1);
    }

    public function test_a_blank_setting_switches_the_alerts_off(): void
    {
        Mail::fake();
        Setting::set('order_notification_email', '');

        app(OrderService::class)->markPaid($this->order());

        Mail::assertNotSent(NewOrderNotificationMail::class);
        // The customer still gets theirs.
        Mail::assertSent(OrderConfirmationMail::class);
    }

    public function test_the_email_carries_what_the_owner_needs_to_post_it(): void
    {
        $order = $this->order();
        $order->forceFill(['status' => 'paid', 'paid_at' => now()])->save();

        $rendered = (new NewOrderNotificationMail($order->load('items')))->render();

        $this->assertStringContainsString('CH-NOTIFY1', $rendered);
        $this->assertStringContainsString('Vitamin C Serum', $rendered);
        $this->assertStringContainsString('12 Test Street', $rendered);
        $this->assertStringContainsString('CV1 2AB', $rendered);
        $this->assertStringContainsString('1st Class Service', $rendered);
        $this->assertStringContainsString('buyer@example.test', $rendered);
        $this->assertStringContainsString(money(4509), $rendered);
    }

    public function test_the_subject_is_scannable_in_an_inbox(): void
    {
        $order = $this->order();

        $envelope = (new NewOrderNotificationMail($order))->build();

        $this->assertSame('New order #CH-NOTIFY1 — '.money(4509), $envelope->subject);
    }

    public function test_a_failing_owner_alert_does_not_break_the_order(): void
    {
        // Same rule as the customer confirmation: the payment has already gone
        // through, so a mail problem must not surface as a 500.
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 1,
        ]);

        $order = $this->order();

        app(OrderService::class)->markPaid($order);

        $this->assertSame('paid', $order->fresh()->status);
    }
}
