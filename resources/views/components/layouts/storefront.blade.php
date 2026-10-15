<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Cardyshub — Let your skin do the talking' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Cardyshub.uk — thoughtfully made skincare, formulated to help you feel confident in the skin you\'re in.' }}">

    <link rel="icon" href="{{ asset('brand/favicon-32.png') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('brand/favicon-192.png') }}" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('brand/favicon-192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Supplementary CSS/JS loaded outside Vite. See the header comment in each
         file: they exist because the Tailwind/Vite bundle cannot be rebuilt on
         the current machine. Fold them back into app.css / app.js once it can. --}}
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>

    {{-- Page-specific styles. Use for rules Tailwind cannot express inline
         (media queries, :target, scroll-margin) rather than a new utility class. --}}
    {{ $head ?? '' }}
</head>
<body class="min-h-screen flex flex-col bg-bone text-ink font-body antialiased overflow-x-hidden">

    {{-- Navigation preloader. A hairline progress bar in the brand pink, shown
         by site.js the moment a link or form starts a real page load, and gone
         with the old document. Decorative, so it is hidden from assistive tech;
         the polite status line next to it is what gets announced. --}}
    <div class="nav-progress" data-nav-progress aria-hidden="true"><span class="nav-progress-bar"></span></div>
    <span class="sr-only" role="status" aria-live="polite" data-nav-progress-status></span>

    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:z-[100] focus:top-2 focus:left-2 focus:bg-white focus:px-4 focus:py-2 focus:rounded focus:shadow-lift">
        Skip to content
    </a>

    <x-marquee :messages="[
        'Your Glow starts here',
        'UK next-day delivery, Monday to Friday',
        'Free UK delivery over '.App\Support\Delivery::freeThreshold(),
        'We ship worldwide',
        'Let your skin do the talking',
    ]" />

    <header data-site-header class="border-b border-line sticky top-0 z-40 bg-bone/95 backdrop-blur">
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8">
            <div class="site-header-bar flex items-center justify-between h-16 sm:h-20 gap-4">
                {{-- The bare x-data is load-bearing: Alpine only processes directives
                     inside a component root, and this button sits outside every other
                     x-data on the page. Without it the @click never binds and the
                     mobile menu cannot be opened at all. --}}
                <button type="button" x-data @click="$dispatch('toggle-mobile-nav')" class="tap-target lg:hidden text-ink -ml-2 p-2">
                    <span class="sr-only">Menu</span>
                    <x-lucide-icon name="menu" class="size-6" />
                </button>

                <a href="{{ route('home') }}" class="tap-target flex items-center shrink-0 mx-auto lg:mx-0">
                    <img src="{{ asset('brand/full-colored.svg') }}" alt="Cardyshub" class="site-header-logo h-9 sm:h-11 w-auto">
                </a>

                <nav class="hidden lg:flex items-center gap-7 text-sm font-medium">
                    <a href="{{ route('shop.index') }}" class="relative py-1 hover:text-pink transition group {{ request()->routeIs('shop.*') ? 'text-pink' : '' }}">
                        Shop
                        <span class="absolute left-0 -bottom-0.5 h-px w-0 bg-pink transition-all duration-200 group-hover:w-full"></span>
                    </a>
                    <a href="{{ route('brands.index') }}" class="relative py-1 hover:text-pink transition group {{ request()->routeIs('brands.*') ? 'text-pink' : '' }}">
                        Brands
                        <span class="absolute left-0 -bottom-0.5 h-px w-0 bg-pink transition-all duration-200 group-hover:w-full"></span>
                    </a>
                    <a href="{{ route('shop.index', ['category' => 'serums']) }}" class="relative py-1 hover:text-pink transition group">
                        Serums
                        <span class="absolute left-0 -bottom-0.5 h-px w-0 bg-pink transition-all duration-200 group-hover:w-full"></span>
                    </a>
                    <a href="{{ route('shop.index', ['category' => 'skincare']) }}" class="relative py-1 hover:text-pink transition group">
                        Skincare
                        <span class="absolute left-0 -bottom-0.5 h-px w-0 bg-pink transition-all duration-200 group-hover:w-full"></span>
                    </a>
                    <a href="{{ route('about') }}" class="relative py-1 hover:text-pink transition group {{ request()->routeIs('about') ? 'text-pink' : '' }}">
                        About
                        <span class="absolute left-0 -bottom-0.5 h-px w-0 bg-pink transition-all duration-200 group-hover:w-full"></span>
                    </a>
                    <a href="{{ route('contact') }}" class="relative py-1 hover:text-pink transition group {{ request()->routeIs('contact') ? 'text-pink' : '' }}">
                        Contact
                        <span class="absolute left-0 -bottom-0.5 h-px w-0 bg-pink transition-all duration-200 group-hover:w-full"></span>
                    </a>
                </nav>

                <div class="flex items-center gap-0.5 sm:gap-2" x-data="{ searchOpen: false }">
                    <button type="button" @click="searchOpen = true" class="tap-target p-2 text-ink hover:text-pink transition">
                        <span class="sr-only">Search</span>
                        <x-lucide-icon name="search" class="size-5" />
                    </button>

                    @auth
                        <a href="{{ route('account.wishlist.index') }}" class="tap-target relative p-2 text-ink hover:text-pink transition hidden sm:inline-flex">
                            <span class="sr-only">Wishlist</span>
                            <x-lucide-icon name="heart" class="size-5" />
                            <span data-wishlist-count
                                  class="count-badge absolute top-0.5 right-0.5 bg-pink text-white text-[10px] font-mono font-bold w-4 h-4 rounded-full flex items-center justify-center {{ ($wishlistCount ?? 0) > 0 ? '' : 'is-empty' }}">{{ $wishlistCount ?? 0 }}</span>
                        </a>
                        <a href="{{ route('dashboard') }}" class="tap-target p-2 text-ink hover:text-pink transition">
                            <span class="sr-only">My account</span>
                            <x-lucide-icon name="user-round" class="size-5" />
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="tap-target p-2 text-ink hover:text-pink transition">
                            <span class="sr-only">Log in</span>
                            <x-lucide-icon name="user-round" class="size-5" />
                        </a>
                    @endauth

                    {{-- The badges always render, hidden at zero by `is-empty`,
                         so site.js only ever has to change their text. --}}
                    <a href="{{ route('cart.index') }}" class="tap-target relative p-2 text-ink hover:text-pink transition" aria-label="Bag">
                        <x-lucide-icon name="shopping-bag" class="size-5" />
                        <span data-cart-count
                              class="count-badge absolute top-0.5 right-0.5 bg-pink text-white text-[10px] font-mono font-bold w-4 h-4 rounded-full flex items-center justify-center {{ ($cartItemCount ?? 0) > 0 ? '' : 'is-empty' }}">{{ $cartItemCount ?? 0 }}</span>
                    </a>

                    {{-- Search overlay. Teleported to <body> for the same reason as
                         the mobile drawer below: the header sets backdrop-blur, which
                         makes it the containing block for fixed children, so inset-0
                         would resolve against the 65px header bar instead of the
                         viewport. Teleporting keeps the Alpine scope but renders it
                         at body level, where `fixed` means what it says. --}}
                    <template x-teleport="body">
                    <div x-show="searchOpen" x-cloak
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         @keydown.escape.window="searchOpen = false"
                         class="fixed inset-0 z-[70] bg-bone">
                        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                            <div class="flex items-center justify-between mb-10">
                                <img src="{{ asset('brand/full-colored.svg') }}" alt="Cardyshub" class="h-9 w-auto">
                                <button type="button" @click="searchOpen = false" class="tap-target p-2 text-ink">
                                    <span class="sr-only">Close search</span>
                                    <x-lucide-icon name="x" class="size-6" />
                                </button>
                            </div>
                            <form method="GET" action="{{ route('shop.index') }}" class="max-w-2xl mx-auto">
                                <label for="global-search" class="sr-only">Search products</label>
                                <div class="relative">
                                    <x-lucide-icon name="search" class="size-6 text-ink-soft absolute left-0 top-1/2 -translate-y-1/2" />
                                    <input x-ref="searchInput" x-effect="searchOpen && $nextTick(() => $refs.searchInput.focus())"
                                           id="global-search" type="search" name="search" placeholder="Search for a serum, a concern, a routine…"
                                           class="w-full border-0 border-b-2 border-line focus:border-pink focus:ring-0 pl-10 pr-2 py-4 text-xl sm:text-2xl font-display bg-transparent">
                                </div>
                            </form>
                        </div>
                    </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Mobile nav drawer --}}
        {{-- Teleported to <body>, and that is not cosmetic. This markup lives
             inside <header>, which sets backdrop-blur; backdrop-filter makes an
             element the containing block for its position:fixed descendants, so
             `inset-y-0` resolved against the 65px header bar rather than the
             viewport. The drawer came out 64px tall, the page showed through
             beneath it, and the nav scrolled 500px of links inside 48px.
             x-teleport moves it to body level while keeping this x-data scope. --}}
        <div x-data="{ open: false }" x-on:toggle-mobile-nav.window="open = !open">
            <template x-teleport="body">
            <div>
            <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-[65] bg-ink/40 lg:hidden"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
            <div x-show="open" x-cloak x-trap.noscroll="open" @keydown.escape.window="open = false"
                 x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-[66] w-[85vw] max-w-xs bg-white shadow-drawer lg:hidden flex flex-col">
                <div class="flex items-center justify-between px-5 py-4 border-b border-line shrink-0">
                    <img src="{{ asset('brand/full-colored.svg') }}" alt="Cardyshub" class="h-9 w-auto">
                    <button type="button" @click="open = false" class="text-ink p-1"><span class="sr-only">Close menu</span><x-lucide-icon name="x" class="size-6" /></button>
                </div>
                <nav class="flex-1 overflow-y-auto px-5 py-6 space-y-1 text-base font-medium">
                    <a href="{{ route('shop.index') }}" class="block py-2.5">Shop all</a>
                    <a href="{{ route('brands.index') }}" class="block py-2.5">Brands</a>
                    @foreach(['skincare' => 'Skincare', 'serums' => 'Serums', 'facial-toners' => 'Facial Toners', 'body-lotions' => 'Body Lotions', 'bath-body' => 'Bath & Body'] as $slug => $label)
                        <a href="{{ route('shop.index', ['category' => $slug]) }}" class="block py-2.5 pl-4 text-sm text-ink-soft">{{ $label }}</a>
                    @endforeach
                    <div class="border-t border-line my-3"></div>
                    <a href="{{ route('about') }}" class="block py-2.5">About</a>
                    <a href="{{ route('contact') }}" class="block py-2.5">Contact</a>
                    @auth
                        <a href="{{ route('account.wishlist.index') }}" class="block py-2.5">Wishlist</a>
                        <a href="{{ route('dashboard') }}" class="block py-2.5">My account</a>
                    @else
                        <a href="{{ route('login') }}" class="block py-2.5">Log in</a>
                    @endauth
                </nav>
            </div>
            </div>
            </template>
        </div>
    </header>

    @if(session('success'))
        <x-toast :message="session('success')" variant="success" />
    @endif
    @if(session('error'))
        <x-toast :message="session('error')" variant="error" />
    @endif

    {{-- Where site.js stacks the toasts it raises for AJAX cart and wishlist
         replies. Same look as x-toast above, which still handles flashed
         session messages for full page loads. --}}
    <div class="toast-stack" data-toast-stack role="status" aria-live="polite"></div>

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-ink text-bone mt-20 sm:mt-24">
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-6 gap-y-10">
                <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                    <img src="{{ asset('brand/full-white.svg') }}" alt="Cardyshub" class="h-10 w-auto mb-4">
                    <p class="font-edito italic text-lg text-bone/80 mb-3 max-w-xs">{{ $siteSettings['site_tagline'] ?? "Let your skin do the talking" }}</p>
                    <p class="text-sm text-bone/80 mb-5 max-w-xs">
                        Honest skincare, clear instructions, and a routine that finally makes sense.
                        Founded in the UK in March 2025.
                    </p>
                    <div class="flex items-center gap-3">
                        @foreach([
                            ['icon' => 'facebook', 'label' => 'Facebook', 'href' => $siteSettings['social_facebook'] ?? 'https://facebook.com/cardyshub.uk'],
                            ['icon' => 'instagram', 'label' => 'Instagram', 'href' => $siteSettings['social_instagram'] ?? 'https://instagram.com/cardyshub.uk'],
                            ['icon' => 'tiktok', 'label' => 'TikTok', 'href' => $siteSettings['social_tiktok'] ?? 'https://tiktok.com/@cardyshub.uk'],
                        ] as $social)
                            <a href="{{ $social['href'] }}" rel="noopener noreferrer" target="_blank"
                               class="social-link text-bone/80 hover:text-white">
                                <span class="sr-only">{{ $social['label'] }}</span>
                                <x-social-icon name="{{ $social['icon'] }}" />
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="font-mono text-xs uppercase tracking-[0.08em] text-bone/50 mb-4">Shop</h3>
                    <ul class="space-y-2.5 text-sm text-bone/80">
                        <li><a href="{{ route('shop.index') }}" class="tap-inline hover:text-white transition">All products</a></li>
                        <li><a href="{{ route('brands.index') }}" class="tap-inline hover:text-white transition">Brands</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'skincare']) }}" class="tap-inline hover:text-white transition">Skincare</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'serums']) }}" class="tap-inline hover:text-white transition">Serums</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'facial-toners']) }}" class="tap-inline hover:text-white transition">Facial toners</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-mono text-xs uppercase tracking-[0.08em] text-bone/50 mb-4">Help</h3>
                    <ul class="space-y-2.5 text-sm text-bone/80">
                        {{-- These four have no routes yet and will 404 until the pages
                             are written. Listed here at the client's instruction. --}}
                        <li><a href="/delivery" class="tap-inline hover:text-white transition">Delivery</a></li>
                        <li><a href="/returns" class="tap-inline hover:text-white transition">Returns and refunds</a></li>
                        <li><a href="{{ route('account.orders.index') }}" class="tap-inline hover:text-white transition">Track my order</a></li>
                        <li><a href="{{ route('contact') }}" class="tap-inline hover:text-white transition">Contact us</a></li>
                        <li><a href="/faqs" class="tap-inline hover:text-white transition">FAQs</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-mono text-xs uppercase tracking-[0.08em] text-bone/50 mb-4">Company</h3>
                    <ul class="space-y-2.5 text-sm text-bone/80">
                        <li><a href="{{ route('about') }}" class="tap-inline hover:text-white transition">About us</a></li>
                        <li><a href="{{ route('about') }}" class="tap-inline hover:text-white transition">Our story</a></li>
                        <li><a href="/wholesale" class="tap-inline hover:text-white transition">Wholesale enquiries</a></li>
                        <li><a href="/terms" class="tap-inline hover:text-white transition">Terms and conditions</a></li>
                        <li><a href="/privacy" class="tap-inline hover:text-white transition">Privacy policy</a></li>
                        <li><a href="/cookies" class="tap-inline hover:text-white transition">Cookie policy</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-mono text-xs uppercase tracking-[0.08em] text-bone/50 mb-4">Account</h3>
                    <ul class="space-y-2.5 text-sm text-bone/80">
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="tap-inline hover:text-white transition">My account</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="tap-inline hover:text-white transition">Sign in</a></li>
                            <li><a href="{{ route('register') }}" class="tap-inline hover:text-white transition">Create an account</a></li>
                        @endauth
                        <li><a href="{{ route('account.orders.index') }}" class="tap-inline hover:text-white transition">My orders</a></li>
                        <li><a href="{{ route('account.wishlist.index') }}" class="tap-inline hover:text-white transition">My wishlist</a></li>
                        <li><a href="{{ route('account.addresses.index') }}" class="tap-inline hover:text-white transition">Addresses</a></li>
                    </ul>
                </div>
            </div>

            {{-- Trust line --}}
            <p class="mt-12 pt-8 border-t border-white/10 font-mono text-xs uppercase tracking-[0.08em] text-bone/50 text-center">
                Free UK delivery over {{ App\Support\Delivery::freeThreshold() }} &middot; 14 day returns on unopened items &middot; Secure checkout
            </p>

            {{-- Payment strip. Stripe only: the card and wallet marks that used
                 to sit here were a promise the checkout could not keep, since
                 which methods actually appear is a Stripe Dashboard setting and
                 varies by customer and device. Naming the processor is accurate
                 whatever is switched on. --}}
            <div class="mt-8 flex flex-wrap items-center justify-center gap-2.5">
                <span class="font-mono text-xs uppercase tracking-[0.08em] text-bone/50">Secure payment</span>
                <span class="pay-mark">Stripe</span>
            </div>
        </div>
        {{-- Legal bar. The registered-company sentence only renders once the
             details exist as settings — the copy deck flags these as client-only
             values, and a storefront must not state invented company details.
             Set: legal_company_name, legal_jurisdiction, legal_company_number,
             legal_registered_office, legal_vat_number --}}
        @php
            $legalName = trim((string) ($siteSettings['legal_company_name'] ?? ''));
            $legalNumber = trim((string) ($siteSettings['legal_company_number'] ?? ''));
            $legalOffice = trim((string) ($siteSettings['legal_registered_office'] ?? ''));
            $legalJurisdiction = trim((string) ($siteSettings['legal_jurisdiction'] ?? 'England and Wales'));
            $legalVat = trim((string) ($siteSettings['legal_vat_number'] ?? ''));
        @endphp
        <div class="border-t border-white/10 py-6">
            <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 text-xs text-bone/50 space-y-2 text-center sm:text-left">
                @if($legalName !== '')
                    <p>
                        {{ $legalName }} trading as Cardyshub.uk.
                        @if($legalNumber !== '') Registered in {{ $legalJurisdiction }}, company number {{ $legalNumber }}. @endif
                        @if($legalOffice !== '') Registered office: {{ $legalOffice }}. @endif
                        @if($legalVat !== '') VAT number {{ $legalVat }}. @endif
                    </p>
                @endif
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p>&copy; {{ now()->year }} Cardyshub.uk. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <span>All prices include VAT where applicable.</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span class="inline-flex items-center gap-1.5"><x-lucide-icon name="shield-check" class="size-3.5" /> Payments secured by Stripe</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- WhatsApp chat. Renders only once a number is configured, so the site can
         never ship a link that opens a chat with someone else's account.
         Set it with: Setting::set('whatsapp_number', '+447700900123') --}}
    @php
        $whatsapp = preg_replace('/\D+/', '', (string) ($siteSettings['whatsapp_number'] ?? ''));
    @endphp
    @if($whatsapp !== '')
        <a class="wa-widget"
           href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Hi Cardyshub, I have a question.') }}"
           target="_blank" rel="noopener noreferrer">
            <span class="sr-only">Chat with us on WhatsApp</span>
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.174.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.247-.694.247-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.82 9.82 0 016.99 2.898 9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            <span class="wa-widget-label" aria-hidden="true">Chat with us</span>
        </a>
    @endif

</body>
</html>
