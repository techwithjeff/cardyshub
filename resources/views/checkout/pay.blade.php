<x-layouts.storefront :title="'Payment — Cardyshub'">

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <a href="{{ route('home') }}" class="inline-flex items-center mb-10">
            <img src="{{ asset('brand/full-colored.svg') }}" alt="Cardyshub" class="h-9 w-auto">
        </a>

        <h1 class="font-display text-3xl text-ink mb-2">Payment</h1>
        <p class="text-ink-soft mb-8">Order #{{ $order->order_number }} &middot; <span class="font-mono">{{ money($order->total_pence) }}</span></p>

        <div id="payment-message" class="hidden mb-4 text-sm text-alert"></div>

        <form id="payment-form">
            <div id="payment-element" class="mb-6"></div>
            <x-button id="submit-button" type="submit" variant="primary" size="lg" class="w-full">
                <span id="button-text">Pay {{ money($order->total_pence) }}</span>
            </x-button>
        </form>

        <p class="text-xs text-ink-faint text-center mt-6 flex items-center justify-center gap-1.5">
            <x-lucide-icon name="shield-check" class="size-4" /> Secured by Stripe
        </p>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe(@json($stripeKey));
        const clientSecret = @json($clientSecret);
        const elements = stripe.elements({ clientSecret });
        const paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');

        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const messageBox = document.getElementById('payment-message');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            messageBox.classList.add('hidden');

            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: @json(route('checkout.confirmation', $order)),
                },
            });

            if (error) {
                messageBox.textContent = error.message;
                messageBox.classList.remove('hidden');
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-busy');
            }
        });
    </script>

</x-layouts.storefront>
