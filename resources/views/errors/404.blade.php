@php
    // The "coming soon" list that used to live here is gone: every page it
    // covered (delivery, returns, faqs, terms, privacy, cookies, wholesale) now
    // has a real route. Reinstate the pattern only if a link is published ahead
    // of its page again.
    $comingSoon = null;
@endphp

<x-layouts.storefront :title="($comingSoon ?? 'Page not found').' — Cardyshub'">

    <section class="about-hero">
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <div class="max-w-narrow">

                @if($comingSoon)
                    <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-4">Coming soon</p>
                    <h1 class="font-display text-4xl sm:text-5xl text-ink mb-5">{{ $comingSoon }} is on its way</h1>
                    <p class="text-lg text-ink-soft mb-4">
                        We have not published this page yet. If you need an answer now, the quick answers
                        on our contact page cover most of it, and we will reply to anything they do not.
                    </p>
                @else
                    <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-4">Error 404</p>
                    <h1 class="font-display text-4xl sm:text-5xl text-ink mb-5">This page has gone missing</h1>
                    <p class="text-lg text-ink-soft mb-4">
                        The link may be out of date, or the address might have a typo in it.
                        Nothing you did wrong.
                    </p>
                @endif

                <p class="text-ink-soft mb-8">Try a search, or pick up from one of these.</p>

                {{-- Search --}}
                <form method="GET" action="{{ route('shop.index') }}" class="mb-10">
                    <label for="notfound-search" class="sr-only">Search products</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <x-lucide-icon name="search" class="size-4 text-ink-faint absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input id="notfound-search" type="search" name="search" autocomplete="off"
                                   placeholder="Search for a product, brand or concern"
                                   class="az-search w-full min-h-[44px] rounded-pill border border-line bg-white pl-10 py-3 text-sm text-ink focus:border-pink focus:ring-pink"
                                   style="padding-right:1rem;">
                        </div>
                        <x-button type="submit" variant="primary">Search</x-button>
                    </div>
                </form>

                {{-- Where to go instead --}}
                <div class="grid sm:grid-cols-2 gap-3 sm:gap-4">
                    @foreach([
                        ['icon' => 'shopping-bag', 'title' => 'Shop all products', 'copy' => 'Every product we stock, filterable by concern and skin type.', 'href' => route('shop.index')],
                        ['icon' => 'sparkles', 'title' => 'Browse the brands', 'copy' => 'The full A to Z of the brands on the shelf.', 'href' => route('brands.index')],
                        ['icon' => 'droplet', 'title' => 'Find your routine', 'copy' => 'Start from what your skin is actually working on.', 'href' => route('home').'#concerns'],
                        ['icon' => 'message-circle', 'title' => 'Talk to us', 'copy' => 'Quick answers, or a real reply within one working day.', 'href' => route('contact')],
                    ] as $link)
                        <a href="{{ $link['href'] }}"
                           class="group flex items-start gap-4 bg-white border border-line rounded-card p-5 hover:border-pink hover:shadow-lift transition-all duration-300">
                            <span class="size-10 shrink-0 rounded-full bg-pink-wash flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                                <x-lucide-icon name="{{ $link['icon'] }}" class="size-5 text-pink" />
                            </span>
                            <span class="min-w-0">
                                <span class="block font-medium text-ink group-hover:text-pink transition">{{ $link['title'] }}</span>
                                <span class="block text-sm text-ink-soft mt-1">{{ $link['copy'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>

                <p class="font-edito italic text-2xl text-pink mt-12">Let your skin do the talking</p>
            </div>
        </div>
    </section>

</x-layouts.storefront>
