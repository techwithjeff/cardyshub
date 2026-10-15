<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Support\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
    ) {}

    public function index()
    {
        $cart = $this->cartService->currentCart(create: false);
        $items = $cart ? $cart->items()->with(['product', 'variant'])->get() : collect();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotalPence = $items->sum(fn ($item) => $item->price_pence * $item->quantity);
        $addresses = Auth::check() ? Auth::user()->addresses()->orderByDesc('is_default')->get() : collect();

        return view('checkout.index', compact('items', 'subtotalPence', 'addresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            // Collection has nothing to post, so no delivery address is asked
            // for and none is required. required_unless rather than nullable:
            // every posted order must still have somewhere to go.
            'line1' => ['required_unless:shipping_method,pickup', 'nullable', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required_unless:shipping_method,pickup', 'nullable', 'string', 'max:120'],
            'postcode' => ['required_unless:shipping_method,pickup', 'nullable', 'string', 'max:20'],
            // UK only for now. We ship worldwide, but international is quoted by
            // email until real per-destination rates are wired in — the methods
            // below are Royal Mail domestic services and would undercharge
            // badly on an overseas parcel.
            'country' => ['required_unless:shipping_method,pickup', 'nullable', 'string', 'in:GB'],
            'shipping_method' => ['required', 'string', Rule::in(collect(Delivery::methods())->pluck('code'))],
            'discount_code' => ['nullable', 'string', 'max:50'],
        ], [
            'country.in' => 'We can only check out UK addresses right now. Email us and we will quote your country.',
            'shipping_method.required' => 'Please choose a delivery option.',
        ]);

        $cart = $this->cartService->currentCart(create: false);
        $items = $cart ? $cart->items : collect();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $discount = null;
        if (! empty($validated['discount_code'])) {
            $discount = Discount::where('code', strtoupper($validated['discount_code']))->first();
            if (! $discount || ! $discount->isValid()) {
                return back()->withInput()->withErrors(['discount_code' => 'This discount code is invalid or expired.']);
            }
        }

        $isPickup = ($validated['shipping_method'] ?? null) === 'pickup';

        // A collection order still gets an address on the record — the shop's,
        // not a blank. The packing slip, the order page and both emails all read
        // the shipping address, so leaving it empty would show an order going
        // nowhere rather than one waiting to be collected.
        $shippingAddress = $isPickup
            ? [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'line1' => 'Collection — '.Delivery::pickupAddress(),
                'line2' => null,
                'city' => null,
                'postcode' => null,
                'country' => 'GB',
            ]
            : [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'line1' => $validated['line1'],
                'line2' => $validated['line2'] ?? null,
                'city' => $validated['city'],
                'postcode' => $validated['postcode'],
                'country' => strtoupper($validated['country']),
            ];

        $order = $this->orderService->createFromCart(
            $cart,
            $shippingAddress,
            $validated['email'],
            $validated['phone'] ?? null,
            $discount,
            $validated['shipping_method']
        );

        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $order->total_pence,
            'currency' => 'gbp',
            'automatic_payment_methods' => ['enabled' => true],
            'receipt_email' => $order->email,
            'metadata' => ['order_id' => $order->id, 'order_number' => $order->order_number],
        ]);

        $order->update(['stripe_payment_intent_id' => $paymentIntent->id]);

        return view('checkout.pay', [
            'order' => $order,
            'clientSecret' => $paymentIntent->client_secret,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function confirmation(Order $order)
    {
        // The customer arrives here straight from Stripe, having just paid.
        // Whatever happens below, they must end up on their order page — so
        // nothing in here is allowed to turn into a 500.
        //
        // This call reaches out to Stripe over the network. On shared hosting
        // that can fail for reasons that have nothing to do with the payment:
        // blocked outbound connections, a cURL timeout, a bad or missing
        // secret key. The webhook marks the order paid independently, so the
        // right response to a failure here is to show the "confirming your
        // payment" state, not an error.
        if ($order->status !== 'paid' && $order->stripe_payment_intent_id) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $paymentIntent = PaymentIntent::retrieve($order->stripe_payment_intent_id);

                if ($paymentIntent->status === 'succeeded') {
                    $this->orderService->markPaid($order);
                    $order->refresh();
                }
            } catch (\Throwable $e) {
                Log::error('Could not confirm payment status with Stripe', [
                    'order_number' => $order->order_number,
                    'payment_intent' => $order->stripe_payment_intent_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('checkout.confirmation', compact('order'));
    }
}
