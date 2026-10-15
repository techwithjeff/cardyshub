@php
    $email = $siteSettings['contact_email'] ?? 'hello@cardyshub.com';

    // Order-related subjects reveal the order number field.
    $subjects = [
        'An order I have placed',
        'Delivery or tracking',
        'Returns or a refund',
        'Product advice',
        'Wholesale or stockist enquiry',
        'Press or collaboration',
        'Something else',
    ];

    $orderSubjects = ['An order I have placed', 'Delivery or tracking', 'Returns or a refund'];

    $faqs = [
        ['id' => 'where', 'title' => 'Where is my order?', 'content' => '<p>You will get a tracking link by email as soon as your parcel leaves us. UK orders placed before '.e(\App\Support\Delivery::cutOffTime()).', Monday to Friday, arrive the next working day. If it has been longer than 3 working days, send us your order number and we will chase it.</p>'],
        ['id' => 'delivery-cost', 'title' => 'How much is delivery?', 'content' => '<p>Delivery is free on UK orders over '.e(\App\Support\Delivery::freeThreshold()).', and costs are shown in full at checkout before you pay. See the <a href="'.route('delivery').'" class="text-pink underline">delivery page</a> for current rates and timings.</p>'],
        ['id' => 'returns', 'title' => 'Can I return something?', 'content' => '<p>Yes. You have 14 days from delivery to tell us, and a further 14 days to send the items back. If your order arrives damaged or incorrect, tell us within 3 days so we can replace it quickly. For hygiene reasons we cannot accept cosmetics once the seal has been broken, unless the product is faulty. Full details are on our <a href="'.route('returns').'" class="text-pink underline">returns page</a>.</p>'],
        ['id' => 'start', 'title' => 'Which product should I start with?', 'content' => '<p>A gentle cleanser, a moisturiser and an SPF. That is a complete routine and it will do more for your skin than any single serum. Once that is a habit, add one active. Start from <a href="'.route('home').'#concerns" class="text-pink underline">Shop by concern</a> if you are not sure where to begin.</p>'],
        ['id' => 'international', 'title' => 'Do you ship outside the UK?', 'content' => '<p>Yes, we ship worldwide. Checkout handles UK addresses at the moment, so email us with what you would like and your delivery address and we will quote your country. Customs duty or import VAT may be charged on arrival by your own country.</p>'],
        ['id' => 'cruelty-free', 'title' => 'Are your products cruelty free or vegan?', 'content' => '<p>Cruelty free and vegan are regulated cosmetics claims, so we only publish them where they can be evidenced. Where a specific product is certified, that is stated on its own product page rather than as a claim across the whole shop.</p>'],
        ['id' => 'sensitive', 'title' => 'I have sensitive skin. Where do I start?', 'content' => '<p>Filter the shop by <a href="'.route('shop.index', ['concern' => 'Sensitivity']).'" class="text-pink underline">Sensitivity</a> and start with one product at a time, leaving a week between introductions so you can tell what is working. If you are unsure, email us and describe your skin. We are happy to help.</p>'],
    ];
@endphp
<x-layouts.storefront :title="'Contact — Cardyshub'">

    {{-- SECTION 1: Page hero --}}
    <section class="about-hero border-b border-line">
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16" data-reveal-section>
            <div class="max-w-narrow">
                <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-4" data-reveal-child>Get in touch</p>
                <h1 class="font-display text-4xl sm:text-5xl text-ink mb-4" data-reveal-child>Talk to us</h1>
                <p class="text-lg text-ink-soft" data-reveal-child>
                    Questions about an order, a product, or where to start? Real people read these,
                    and we usually reply within one working day.
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
        <div class="grid lg:grid-cols-[54%_46%] gap-12 lg:gap-10">

            {{-- SECTION 3: Contact form --}}
            <div>
                <h2 class="font-display text-2xl sm:text-3xl text-ink mb-3">Send us a message</h2>
                <p class="text-ink-soft mb-8">
                    Tell us as much as you can and we will get you a proper answer rather than a holding reply.
                    If it is about an order, include your order number and we can look it up straight away.
                </p>

                @if ($errors->any())
                    <div class="mb-6 rounded-card border border-alert bg-white p-4 text-sm text-alert">
                        <p class="font-medium mb-1">That did not send.</p>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-ink-soft">Or email us directly at <a href="mailto:{{ $email }}" class="text-pink underline">{{ $email }}</a>.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="space-y-5"
                      x-data="{ subject: '{{ old('subject') }}', orderSubjects: {{ Js::from($orderSubjects) }},
                                get needsOrder() { return this.orderSubjects.includes(this.subject) } }">
                    @csrf

                    <x-form.input label="Name" name="name" required :value="old('name')" placeholder="Your name" :error="$errors->first('name')" />
                    <x-form.input label="Email" type="email" name="email" required :value="old('email')" placeholder="your@email.com" :error="$errors->first('email')" />

                    <x-form.select label="What is this about?" name="subject" required x-model="subject"
                                   :options="['' => 'Choose one'] + array_combine($subjects, $subjects)"
                                   :error="$errors->first('subject')" />

                    {{-- Only relevant to order-related subjects, so it stays out of the
                         way until one is chosen. Plain x-show rather than x-collapse:
                         the field must appear reliably, and a height animation is not
                         worth the extra failure mode on a required-path form. --}}
                    <div x-show="needsOrder" x-cloak>
                        <x-form.input label="Order number (optional)" name="order_number" :value="old('order_number')" placeholder="e.g. CH1042" :error="$errors->first('order_number')" />
                    </div>

                    <div>
                        <label for="contact-message" class="block text-sm font-medium text-ink mb-1.5">Message</label>
                        <textarea id="contact-message" name="message" rows="6" required placeholder="Tell us what you need"
                                  class="w-full rounded-card border-line focus:border-pink focus:ring-pink">{{ old('message') }}</textarea>
                    </div>

                    <x-button type="submit" variant="primary" size="lg">Send message</x-button>

                    <p class="text-xs text-ink-faint leading-relaxed">
                        We use your details only to answer your message. We will not add you to any
                        mailing list unless you ask us to. See our <a href="/privacy" class="underline hover:text-pink">privacy policy</a> for more.
                    </p>
                </form>
            </div>

            {{-- SECTION 2: Contact details --}}
            <div>
                <div class="bg-white border border-line rounded-card p-6 sm:p-8 mb-10">
                    <h2 class="font-display text-xl text-ink mb-5">Contact details</h2>

                    <dl class="space-y-5 text-sm">
                        <div>
                            <dt class="font-mono text-xs uppercase tracking-[0.08em] text-ink-faint mb-1.5">Email</dt>
                            <dd><a href="mailto:{{ $email }}" class="tap-inline text-pink font-medium hover:underline">{{ $email }}</a></dd>
                        </div>
                        <div>
                            <dt class="font-mono text-xs uppercase tracking-[0.08em] text-ink-faint mb-1.5">Response time</dt>
                            <dd class="text-ink-soft leading-relaxed">Monday to Friday, 9am to 5pm UK time. Messages sent at the weekend are answered on Monday.</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-xs uppercase tracking-[0.08em] text-ink-faint mb-2">Social</dt>
                            <dd class="flex items-center gap-2.5">
                                @foreach([
                                    ['icon' => 'instagram', 'label' => 'Instagram', 'href' => $siteSettings['social_instagram'] ?? 'https://instagram.com/cardyshub.uk'],
                                    ['icon' => 'tiktok', 'label' => 'TikTok', 'href' => $siteSettings['social_tiktok'] ?? 'https://tiktok.com/@cardyshub.uk'],
                                    ['icon' => 'facebook', 'label' => 'Facebook', 'href' => $siteSettings['social_facebook'] ?? 'https://facebook.com/cardyshub.uk'],
                                ] as $social)
                                    <a href="{{ $social['href'] }}" target="_blank" rel="noopener noreferrer"
                                       class="social-link social-link-light text-ink-soft hover:text-pink">
                                        <span class="sr-only">{{ $social['label'] }}</span>
                                        <x-social-icon name="{{ $social['icon'] }}" />
                                    </a>
                                @endforeach
                            </dd>
                        </div>

                        {{-- Company details render only once they exist as settings; the
                             copy deck flags them as client-only values. --}}
                        @if(trim((string) ($siteSettings['legal_company_name'] ?? '')) !== '')
                            <div>
                                <dt class="font-mono text-xs uppercase tracking-[0.08em] text-ink-faint mb-1.5">Company</dt>
                                <dd class="text-ink-soft leading-relaxed">
                                    {{ $siteSettings['legal_company_name'] }}<br>
                                    @if(!empty($siteSettings['legal_registered_office'])){{ $siteSettings['legal_registered_office'] }}<br>@endif
                                    @if(!empty($siteSettings['legal_company_number']))Company number {{ $siteSettings['legal_company_number'] }}@endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- SECTION 4: Before you write --}}
                <h2 class="font-display text-2xl text-ink mb-2">Quick answers</h2>
                <p class="text-sm text-ink-soft mb-5">Most questions are answered here faster than we can reply.</p>
                <x-accordion :items="$faqs" />
            </div>
        </div>
    </div>

</x-layouts.storefront>
