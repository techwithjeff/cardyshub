@php
    use App\Http\Controllers\WholesaleController;
    use App\Models\Setting;

    $support = Setting::get('contact_email', 'hello@cardyshub.com');
    $wholesaleEmail = trim((string) Setting::get('wholesale_email', ''));
    $types = array_combine(WholesaleController::BUSINESS_TYPES, WholesaleController::BUSINESS_TYPES);
    $values = array_combine(WholesaleController::ORDER_VALUES, WholesaleController::ORDER_VALUES);
@endphp

<x-policy-page
    title="Stock Cardyshub"
    eyebrow="Wholesale enquiries"
    intro="Cardyshub is a British skincare brand built on a simple belief: good skin does not need to be argued for. It speaks for itself."
    :requires="['wholesale_email', 'wholesale_moq']"
    :toc="[
        'why' => 'Why stock us',
        'how' => 'How it works',
        'terms' => 'Trade terms',
        'also' => 'Also worth asking',
        'enquiry' => 'Enquiry form',
    ]">

    <p>We work with a considered number of stockists across the UK, from independent beauty boutiques and salons to spas, concept stores and online retailers. If your customers care about what goes onto their skin, we would like to hear from you.</p>

    <h2 id="why">Why stock us</h2>

    <p><strong>A brand with a point of view.</strong> Cardyshub was founded by {{ Setting::get('about_founder_name', 'Abibat Otaobayomi Cardoso') }} around a single idea, that skincare should be honest about what it does. Our customers stay because the products work, not because the packaging shouted loudest.</p>

    <p><strong>Products that sell through.</strong> Our range is deliberately tight. Fewer products, each earning its place on the shelf, means faster stock turn and less capital tied up in slow movers.</p>

    <p><strong>Margins that make sense.</strong> We offer competitive trade pricing with volume tiers, so your margin improves as your order grows.</p>

    <p><strong>Marketing you can use.</strong> Approved product photography, lifestyle imagery, ingredient breakdowns and copy are supplied free to every stockist. You should not have to build a content library to sell someone else's brand.</p>

    <p><strong>A real person on the other end.</strong> You will deal directly with our team. No portal tickets, no queue.</p>

    <h2 id="how">How it works</h2>

    {{-- Built in PHP rather than with an inline @if: Blade's directive matcher
         uses \B, so `below@if(...)` is not recognised as a directive while its
         @endif still is, which breaks the compiled template. --}}
    @php
        $emailClause = $wholesaleEmail !== ''
            ? ' or email us at <a href="mailto:'.e($wholesaleEmail).'">'.e($wholesaleEmail).'</a>'
            : '';
    @endphp
    <p><strong>1. Tell us about your business.</strong> Complete the form below{!! $emailClause !!}. Include your business name, location, website or social handles, and a sense of your existing range.</p>

    <p><strong>2. We review and respond.</strong> We aim to reply within two working days. We do check that stockists are a genuine fit, both for you and for us, so this is a conversation rather than a form submission.</p>

    <p><strong>3. Receive your trade pack.</strong> Once approved you will get our full wholesale price list, minimum order terms, product specifications and marketing assets.</p>

    <p><strong>4. Place your first order.</strong> Opening orders start at <x-policy-value key="wholesale_moq" label="MOQ to confirm" />. Lead time is typically five to seven working days from confirmed payment.</p>

    <h2 id="terms">Trade terms at a glance</h2>

    <ul>
        <li><strong>Minimum opening order:</strong> <x-policy-value key="wholesale_moq" label="MOQ to confirm" /></li>
        <li><strong>Reorder minimum:</strong> <x-policy-value key="wholesale_moq" label="MOQ to confirm" /></li>
        <li><strong>Lead time:</strong> 5 to 7 working days</li>
        <li><strong>Payment:</strong> Pro forma invoice for opening orders. Credit terms available to established accounts on request</li>
        <li><strong>Delivery:</strong> Free on trade orders above {{ App\Support\Delivery::freeThreshold() }}. Charged at cost below this</li>
        <li><strong>Territory:</strong> United Kingdom. Contact us to discuss international distribution</li>
    </ul>

    <h2 id="also">Also worth talking to us about</h2>

    <p><strong>Salons and spas.</strong> Professional-size formats and treatment room pricing are available. Ask about our back-bar terms.</p>

    <p><strong>Corporate and gifting.</strong> Bulk orders for hampers, events, hotel amenities and employee gifting. Custom presentation available on larger runs.</p>

    <p><strong>Subscription boxes and curators.</strong> We supply sampling formats for beauty box partnerships.</p>

    <h2 id="enquiry">Enquiry form</h2>

    @if (session('success'))
        <div class="rounded-card border border-line bg-white p-5 text-sm text-ink">
            {{ session('success') }}
        </div>
    @else
        @if ($errors->any())
            <div class="rounded-card border border-alert bg-white p-4 text-sm text-alert mb-6">
                <p class="font-medium mb-1">That did not send.</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('wholesale.store') }}" class="space-y-5">
            @csrf

            <div class="grid sm:grid-cols-2 gap-5">
                <x-form.input label="Your name" name="name" required :value="old('name')" :error="$errors->first('name')" />
                <x-form.input label="Business name" name="business_name" required :value="old('business_name')" :error="$errors->first('business_name')" />
            </div>

            <x-form.select label="Business type" name="business_type" required
                           :options="array_merge(['' => 'Choose one'], $types)"
                           :error="$errors->first('business_type')" />

            <div class="grid sm:grid-cols-2 gap-5">
                <x-form.input label="Email address" type="email" name="email" required :value="old('email')" :error="$errors->first('email')" />
                <x-form.input label="Phone number" name="phone" :value="old('phone')" :error="$errors->first('phone')" />
            </div>

            <x-form.input label="Website or Instagram handle" name="website" :value="old('website')" :error="$errors->first('website')" />

            <div>
                <label for="wholesale-address" class="block text-sm font-medium text-ink mb-1.5">Business address, including city and postcode</label>
                <textarea id="wholesale-address" name="business_address" rows="3"
                          class="w-full rounded-card border-line focus:border-pink focus:ring-pink">{{ old('business_address') }}</textarea>
            </div>

            <x-form.select label="Approximate opening order value" name="opening_order_value"
                           :options="array_merge(['' => 'Choose one'], $values)"
                           :error="$errors->first('opening_order_value')" />

            <div>
                <label for="wholesale-message" class="block text-sm font-medium text-ink mb-1.5">Tell us about your business and your customers</label>
                <textarea id="wholesale-message" name="message" rows="6" required
                          class="w-full rounded-card border-line focus:border-pink focus:ring-pink">{{ old('message') }}</textarea>
            </div>

            <label class="flex items-start gap-3 text-sm text-ink-soft">
                <input type="checkbox" name="consent" value="1" required
                       class="mt-0.5 size-4 rounded border-line text-pink-btn focus:ring-pink shrink-0">
                <span>I agree to Cardyshub contacting me about this enquiry and I have read the <a href="{{ route('privacy') }}">Privacy Policy</a>.</span>
            </label>

            <x-button type="submit" variant="primary" size="lg">Send enquiry</x-button>
        </form>
    @endif

</x-policy-page>
