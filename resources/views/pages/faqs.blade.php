@php
    use App\Models\Setting;
    use App\Support\Delivery;

    $support = Setting::get('contact_email', 'hello@cardyshub.com');
    $privacyEmail = trim((string) Setting::get('privacy_email', '')) ?: $support;

    $groups = [
        [
            'title' => 'Ordering',
            'icon' => 'shopping-bag',
            'items' => [
                ['Do I need an account to order?', '<p>No. You can check out as a guest. Creating an account simply means your details are saved for next time and you can track orders and returns in one place.</p>'],
                ['Can I change or cancel my order?', '<p>If it has not been dispatched, yes. Email <a href="mailto:'.$support.'">'.$support.'</a> with your order number as soon as you can. Once a parcel is with the carrier we cannot recall it, but you can return it under our <a href="'.route('returns').'">returns policy</a>.</p>'],
                ['I did not get a confirmation email.', '<p>Check your spam or promotions folder first. If it is not there, the email address may have been mistyped at checkout. Contact us with your name and approximate order time and we will find it.</p>'],
                ['Do you have a physical store?', '<p>Not at present. Cardyshub is online only, though our products are stocked by selected retailers. If you run a business and would like to stock us, see our <a href="'.route('wholesale').'">Wholesale Enquiries</a> page.</p>'],
            ],
        ],
        [
            'title' => 'Payment',
            'icon' => 'shield-check',
            'items' => [
                ['Which payment methods do you accept?', '<p>We accept all major debit and credit cards, along with Apple Pay, Google Pay and Link. Payments are processed by Stripe.</p>'],
                ['Is my payment secure?', '<p>Yes. All payments are handled by Stripe, one of the most widely used payment processors in the world. Your full card details are never stored on our servers and never pass through them.</p>'],
                ['Do you accept PayPal or Klarna?', '<p>Not currently.</p>'],
                ['Why has my card been declined?', '<p>Most declines come from the bank rather than from us, usually because of a security check, an address mismatch or available funds. Check the billing address matches your card exactly, then try again or use a different card.</p>'],
                ['Which currency are prices in?', '<p>All prices are in pounds sterling and include VAT where applicable.</p>'],
            ],
        ],
        [
            'title' => 'Delivery',
            'icon' => 'truck',
            'items' => [
                ['How long will my order take?', '<p>Order today, glow tomorrow. UK orders placed before '.e(Delivery::cutOffTime()).', Monday to Friday, arrive the next working day on our standard service. Order at the weekend and it arrives on Tuesday.</p>'],
                ['How much is delivery?', '<p>'.e(Delivery::standardMethod()->name).' is '.e(Delivery::standardMethod()->price()).' and is free on orders over '.e(Delivery::freeThreshold()).'. '.count(Delivery::postedMethods()).' Royal Mail services are offered at checkout, from '.e(Delivery::cheapestMethod()->price()).', including tracked, signed for and guaranteed timed delivery. Full detail is on the <a href="'.route('delivery').'">Delivery page</a>.</p>'],
                ['Can I collect my order instead?', '<p>Yes, and it is free. Choose <strong>'.e(Delivery::pickupMethod()->name).'</strong> at checkout rather than a postage option, and no delivery address is needed. Collection is from '.e(Delivery::pickupAddress()).', and we will email you as soon as your order is ready &mdash; usually within 2 working days. Please wait for that email before travelling.</p>'],
                ['Do you deliver outside the UK?', '<p>Yes, we ship worldwide. Checkout currently handles UK addresses only, so email us with your order and address and we will quote your country.</p>'],
                ['How do I track my order?', '<p>Your dispatch email contains a tracking link. You can also sign in and open <a href="'.route('account.orders.index').'">My Orders</a>.</p>'],
            ],
        ],
        [
            'title' => 'Returns',
            'icon' => 'rotate-ccw',
            'items' => [
                ['Can I return a product I have opened?', '<p>Not for a change of mind, because skincare is a hygiene product and we cannot resell it. If the product is faulty, damaged or not as described, you can return it whether it is opened or not.</p>'],
                ['How long do I have to return something?', '<p>14 days from delivery to tell us, then 14 days to send it back. If your order arrives damaged or incorrect, please tell us within 3 days so we can claim against the carrier and replace it quickly. For faulty items you still have 30 days to reject and receive a full refund.</p>'],
                ['When will I get my refund?', '<p>We process refunds within 14 days of receiving the return, and usually much faster. Your bank then takes a further 3 to 5 working days to show it.</p>'],
                ['Who pays return postage?', '<p>You do for a change of mind. We do if the item is faulty, damaged or incorrect.</p>'],
            ],
        ],
        [
            'title' => 'Products and skin',
            'icon' => 'droplet',
            'items' => [
                ['Are your products suitable for sensitive skin?', '<p>Many are, but skin varies. Every product page lists the full ingredients. If you have a known sensitivity or allergy, read the list carefully and patch test before full use.</p>'],
                ['How do I patch test?', '<p>Apply a small amount to the inside of your forearm or behind your ear. Leave it for 24 hours. If there is no redness, itching or irritation, the product is likely to suit you.</p>'],
                ['How should I store my products?', '<p>Somewhere cool and dry, out of direct sunlight. Bathrooms are humid and warm, which shortens the life of most formulations, so a bedroom shelf or drawer is better.</p>'],
                ['How long does a product last once opened?', '<p>Look for the small open jar symbol on the packaging. The number inside it, such as 6M or 12M, is the number of months the product stays at its best after opening.</p>'],
                ['Where do I find the batch code and expiry?', '<p>Printed on the base of the bottle or the crimp of the tube. Please quote it if you contact us about a product.</p>'],
                ['In what order should I apply my skincare?', '<p>As a general rule, work from the thinnest texture to the thickest: cleanse, tone, serum, moisturiser, then sunscreen in the morning. Individual product pages give specific guidance.</p>'],
                ['Are your products cruelty free or vegan?', '<p>These are regulated cosmetics claims, so we only publish them where they can be evidenced. Where a specific product is certified, that is stated on its own product page rather than as a claim across the whole shop.</p>'],
            ],
        ],
        [
            'title' => 'Your account and data',
            'icon' => 'user-round',
            'items' => [
                ['How do I reset my password?', '<p>Use the Forgot Password link on the <a href="'.route('login').'">sign in page</a>. A reset link will be emailed to you.</p>'],
                ['How do I unsubscribe from emails?', '<p>Use the unsubscribe link at the foot of any marketing email, or email <a href="mailto:'.$support.'">'.$support.'</a>. This takes effect immediately and does not affect order-related emails.</p>'],
                ['How do I delete my account?', '<p>Email <a href="mailto:'.$privacyEmail.'">'.$privacyEmail.'</a> and we will delete it. We are required to retain order and transaction records for six years for tax purposes, but nothing else is kept.</p>'],
            ],
        ],
    ];

    $total = collect($groups)->sum(fn ($g) => count($g['items']));
@endphp

<x-layouts.storefront :title="'Frequently asked questions — Cardyshub'">

    <x-slot:head>
        <style>
            .faq-hero-search { max-width: 30rem; }
        </style>
    </x-slot:head>

    <div x-data="{
        q: '',
        get needle() { return this.q.trim().toLowerCase() },
        hit(text) { return ! this.needle || text.toLowerCase().includes(this.needle) },
        anyIn(list) { return list.some(t => this.hit(t)) },
        open: null,
        toggle(id) { this.open = (this.open === id ? null : id) },
    }">

        {{-- Hero --}}
        <section class="about-hero border-b border-line">
            <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <nav aria-label="Breadcrumb" class="breadcrumb text-sm text-ink-soft mb-3">
                    <a href="{{ route('home') }}" class="hover:text-pink">Home</a> &rsaquo; <span class="text-ink">FAQs</span>
                </nav>
                <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-3">Help centre</p>
                <h1 class="font-display text-4xl sm:text-5xl text-ink mb-4">Frequently asked questions</h1>
                <p class="text-lg text-ink-soft max-w-narrow mb-8">
                    {{ $total }} answers, grouped by what you are trying to do. If yours is not here, a person will reply.
                </p>

                {{-- Search replaces the old contents list: more useful at this length, --}}
                {{-- and it filters the groups below as you type. --}}
                <div class="faq-hero-search">
                    <label for="faq-search" class="sr-only">Search the questions</label>
                    <div class="relative">
                        <x-lucide-icon name="search" class="size-4 text-ink-faint absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <input id="faq-search" type="search" x-model="q" autocomplete="off"
                               placeholder="Search the questions"
                               class="faq-search w-full min-h-[44px] rounded-pill border border-line bg-white pl-10 py-3 text-sm text-ink focus:border-pink focus:ring-pink"
                               style="padding-right:1rem;">
                    </div>
                </div>
            </div>
        </section>

        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <div class="max-w-narrow">

                @foreach($groups as $gi => $group)
                    @php $questions = collect($group['items'])->map(fn ($i) => $i[0].' '.strip_tags($i[1]))->values()->all(); @endphp

                    <section class="faq-group" x-show="anyIn({{ Js::from($questions) }})">
                        <div class="faq-group-head">
                            <span class="faq-group-icon" aria-hidden="true">
                                <x-lucide-icon name="{{ $group['icon'] }}" />
                            </span>
                            <h2 class="faq-group-title">{{ $group['title'] }}</h2>
                            <span class="faq-count">{{ count($group['items']) }} {{ Str::plural('answer', count($group['items'])) }}</span>
                        </div>

                        @foreach($group['items'] as $qi => $item)
                            @php $id = $gi.'-'.$qi; $haystack = $item[0].' '.strip_tags($item[1]); @endphp
                            <div class="faq-item" x-show="hit({{ Js::from($haystack) }})">
                                <button type="button" class="faq-q"
                                        @click="toggle('{{ $id }}')"
                                        :aria-expanded="open === '{{ $id }}' ? 'true' : 'false'"
                                        aria-controls="faq-panel-{{ $id }}">
                                    <span>{{ $item[0] }}</span>
                                    <x-lucide-icon name="chevron-down" />
                                </button>
                                <div id="faq-panel-{{ $id }}" class="faq-a" x-show="open === '{{ $id }}'" x-cloak>
                                    {!! $item[1] !!}
                                </div>
                            </div>
                        @endforeach
                    </section>
                @endforeach

                {{-- No matches --}}
                <p x-show="needle && ! anyIn({{ Js::from(collect($groups)->flatMap(fn ($g) => collect($g['items'])->map(fn ($i) => $i[0].' '.strip_tags($i[1])))->values()->all()) }})"
                   x-cloak class="text-center text-ink-soft py-14">
                    Nothing matches &ldquo;<span x-text="q"></span>&rdquo;. Try a different word, or
                    <a href="{{ route('contact') }}" class="text-pink underline">ask us directly</a>.
                </p>

                {{-- Still stuck --}}
                <div class="mt-10 grid sm:grid-cols-2 gap-4">
                    <a href="{{ route('contact') }}"
                       class="group flex items-start gap-4 bg-white border border-line rounded-card p-5 hover:border-pink hover:shadow-lift transition-all duration-300">
                        <span class="size-10 shrink-0 rounded-full bg-pink-wash flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                            <x-lucide-icon name="message-circle" class="size-5 text-pink" />
                        </span>
                        <span class="min-w-0">
                            <span class="block font-medium text-ink group-hover:text-pink transition">Send us a message</span>
                            <span class="block text-sm text-ink-soft mt-1">A person reads every one, usually replying within a working day.</span>
                        </span>
                    </a>
                    <a href="mailto:{{ $support }}"
                       class="group flex items-start gap-4 bg-white border border-line rounded-card p-5 hover:border-pink hover:shadow-lift transition-all duration-300">
                        <span class="size-10 shrink-0 rounded-full bg-pink-wash flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                            <x-lucide-icon name="mail" class="size-5 text-pink" />
                        </span>
                        <span class="min-w-0">
                            <span class="block font-medium text-ink group-hover:text-pink transition">Email us</span>
                            <span class="block text-sm text-ink-soft mt-1">{{ $support }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</x-layouts.storefront>
