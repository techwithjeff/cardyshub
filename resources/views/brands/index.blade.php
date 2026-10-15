<x-layouts.storefront :title="'Brands — Cardyshub'">

    <x-slot:head>
        <style>
            /* The A-Z rail sticks directly under the site header, which is 4rem
               tall on mobile and 5rem from sm up. This build of the stylesheet has
               no top-16/top-20 utility compiled, so the offset lives here. */
            .az-rail { position: sticky; top: 4rem; z-index: 30; }
            /* On a phone, 22 letters wrap to four rows and swallow a fifth of the
               screen. Keep them on one scrollable line until there is room to wrap. */
            .az-letters { display: flex; flex-wrap: nowrap; gap: 0.25rem; overflow-x: auto; scrollbar-width: none; }
            .az-letters::-webkit-scrollbar { display: none; }
            .az-anchor { scroll-margin-top: 8.5rem; }
            @media (min-width: 640px) {
                .az-rail { top: 5rem; }
                .az-letters { flex-wrap: wrap; overflow-x: visible; }
                .az-anchor { scroll-margin-top: 10rem; }
            }
            .az-anchor:target .az-letter { color: #FA0467; }
            .az-search::placeholder { color: #7A6875; }
        </style>
    </x-slot:head>

    @php
        // Group A-Z. Anything not starting with a letter (none today, but names
        // like "3INA" are common in beauty) collects under #.
        $grouped = $brands->groupBy(function ($brand) {
            $first = strtoupper(substr($brand->name, 0, 1));

            return ctype_alpha($first) ? $first : '#';
        })->sortKeys();

        $stocked = $brands->where('products_count', '>', 0);
        $allNames = $brands->pluck('name')->values()->all();
    @endphp

    <div x-data="{
        q: '',
        get needle() { return this.q.trim().toLowerCase() },
        matches(name) { return ! this.needle || name.toLowerCase().includes(this.needle) },
        anyIn(names) { return names.some(n => this.matches(n)) },
    }">

        {{-- Masthead --}}
        <div class="bg-pink-wash border-b border-line">
            <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
                <nav aria-label="Breadcrumb" class="breadcrumb text-sm text-ink-soft mb-3">
                    <a href="{{ route('home') }}" class="hover:text-pink">Home</a> &rsaquo; <span class="text-ink">Brands</span>
                </nav>

                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="flex-1 min-w-0">
                        <h1 class="font-display text-4xl text-ink mb-2">Shop by brand</h1>
                        <p class="text-ink-soft">
                            {{ $brands->count() }} brands, A to Z &mdash; {{ $stocked->count() }} with products in stock right now.
                        </p>
                    </div>

                    <div class="w-full sm:w-64 shrink-0">
                        <label for="brand-search" class="sr-only">Search brands</label>
                        <div class="relative">
                            <x-lucide-icon name="search" class="size-4 text-ink-faint absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input id="brand-search" type="search" x-model="q" autocomplete="off"
                                   placeholder="Search brands"
                                   class="az-search w-full rounded-pill border border-line bg-white pl-10 py-3 text-sm text-ink focus:border-pink focus:ring-pink"
                                   style="padding-right:1rem;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- A-Z rail --}}
        <div class="az-rail bg-bone/95 backdrop-blur border-b border-line">
            <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="az-letters items-center">
                    @foreach($grouped->keys() as $letter)
                        <a href="#letter-{{ $letter === '#' ? 'other' : strtolower($letter) }}"
                           class="size-9 shrink-0 rounded-full flex items-center justify-center text-sm font-medium text-ink-soft hover:bg-pink hover:text-white transition-colors">
                            {{ $letter }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">

            {{-- The brands you can actually buy from today --}}
            @if($stocked->isNotEmpty())
                <section class="mb-10" x-show="! needle" data-reveal-section>
                    <x-section-heading eyebrow="In stock now">Brands with products</x-section-heading>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                        @foreach($stocked as $brand)
                            <a href="{{ route('brands.show', $brand) }}"
                               class="group flex flex-col items-center text-center bg-white rounded-card border border-line p-5 hover:border-pink hover:shadow-lift transition-all duration-300" data-reveal-child>
                                <div class="size-12 sm:size-14 rounded-full bg-pink-wash flex items-center justify-center mb-3 overflow-hidden transition-transform duration-300 group-hover:scale-110">
                                    @if($brand->logo)
                                        <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="font-display text-lg text-pink">{{ Str::of($brand->name)->substr(0, 1) }}</span>
                                    @endif
                                </div>
                                <p class="text-xs sm:text-sm font-medium text-ink group-hover:text-pink transition line-clamp-2">{{ $brand->name }}</p>
                                <p class="text-xs text-ink-faint mt-1">{{ $brand->products_count }} {{ Str::plural('product', $brand->products_count) }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Full A-Z --}}
            @foreach($grouped as $letter => $group)
                @php $names = $group->pluck('name')->values()->all(); @endphp
                <section id="letter-{{ $letter === '#' ? 'other' : strtolower($letter) }}"
                         class="az-anchor mb-10"
                         x-show="anyIn({{ Js::from($names) }})">
                    <div class="flex items-baseline gap-4 border-b border-line pb-3 mb-6">
                        <h2 class="az-letter font-display text-3xl text-ink">{{ $letter }}</h2>
                        <span class="text-xs text-ink-faint">{{ $group->count() }} {{ Str::plural('brand', $group->count()) }}</span>
                    </div>

                    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-2">
                        @foreach($group as $brand)
                            <li x-show="matches({{ Js::from($brand->name) }})">
                                <a href="{{ route('brands.show', $brand) }}"
                                   class="group flex items-baseline justify-between gap-3 py-2.5 border-b border-line hover:border-pink transition-colors">
                                    <span class="text-sm font-medium {{ $brand->products_count ? 'text-ink' : 'text-ink-faint' }} group-hover:text-pink transition">
                                        {{ $brand->name }}
                                    </span>
                                    <span class="text-xs text-ink-faint shrink-0">
                                        {{ $brand->products_count ?: 'Soon' }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach

            <p x-show="needle && ! anyIn({{ Js::from($allNames) }})" x-cloak
               class="text-center text-ink-soft py-16">
                No brands match &ldquo;<span x-text="q"></span>&rdquo;.
            </p>
        </div>
    </div>

</x-layouts.storefront>
