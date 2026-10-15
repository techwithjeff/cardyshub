@props(['slides' => []])

<section x-data="{
        active: 0,
        count: {{ count($slides) }},
        timer: null,
        start() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            this.timer = setInterval(() => this.next(), 6000);
        },
        stop() { clearInterval(this.timer); },
        next() { this.active = (this.active + 1) % this.count; },
        goTo(i) { this.active = i; this.stop(); this.start(); },
    }"
    x-init="start()"
    @mouseenter="stop()" @mouseleave="start()"
    class="relative overflow-hidden">

    {{-- hero-stack (site.css) puts every slide in one CSS grid cell instead of
         absolutely positioning them, so the active slide's content sets the
         height. With `absolute inset-0` the height was fixed and anything taller
         was clipped — at 320px that hid both CTA buttons entirely. --}}
    <div class="hero-stack relative">
        @foreach($slides as $i => $slide)
            @php
                $theme = $slide['theme'] ?? 'gold';
                // A photographic background only takes over from lg up, where the hero's
                // aspect ratio matches the shot. Below that the gradient still runs.
                $photo = $slide['photo'] ?? null;
                $photoOnly = $photo ? 'lg:hidden' : '';
                $photoSrcset = $photo
                    ? collect($photo['srcset'])->map(fn ($file, $w) => asset($file) . " {$w}w")->implode(', ')
                    : '';

                // Gold and pink live in app.css; sage is inlined so a new theme can be added
                // without a Tailwind rebuild. Ornament colours travel with the theme.
                $themes = \App\Support\HeroTheme::all();
                $t = $themes[$theme] ?? $themes['gold'];
            @endphp
            <div x-show="active === {{ $i }}" x-cloak
                 x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="hero-slide relative overflow-hidden {{ $t['class'] }}"
                 @if($t['gradient']) style="background: {{ $t['gradient'] }};" @endif>

                @if($photo)
                    {{-- The <source> media query is what gates the download: below lg it never
                         matches, so the browser loads the 1x1 fallback and fetches nothing.
                         A plain display:none <img> would still be fetched on mobile. --}}
                    <picture>
                        <source media="(min-width: 1024px)" srcset="{{ $photoSrcset }}" sizes="100vw" type="image/webp">
                        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                             alt="" aria-hidden="true"
                             width="{{ $photo['w'] }}" height="{{ $photo['h'] }}"
                             @if($i === 0) fetchpriority="high" @else fetchpriority="low" @endif
                             {{-- Centred crop keeps the product cluster in the right-hand grid column --}}
                             class="hidden lg:block absolute inset-0 w-full h-full object-cover">
                    </picture>
                @endif

                {{-- Giant faint brand watermark --}}
                <img src="{{ asset('brand/icon-colored.svg') }}" alt="" aria-hidden="true"
                     class="hero-watermark w-[70vh] h-[70vh] -right-[18vh] -top-[10vh] rotate-12 hidden sm:block {{ $photoOnly }}">

                {{-- Decorative bokeh / petals. Only ever seen below lg when a photo is set. --}}
                @foreach($t['bokeh'] as $b)
                    <span class="hero-bokeh {{ $photoOnly }}"
                          style="background:{{ $b['color'] }};width:{{ $b['size'] }}px;height:{{ $b['size'] }}px;top:{{ $b['top'] }};left:{{ $b['left'] }};animation-delay:{{ $b['delay'] }}s;@isset($b['opacity'])opacity:{{ $b['opacity'] }};@endisset"></span>
                @endforeach
                @foreach($t['petals'] as $p)
                    <span class="hero-petal {{ $photoOnly }}"
                          style="background:{{ $p['color'] }};width:{{ $p['w'] }}px;height:{{ $p['h'] }}px;border-radius:0 60% 0 60%;top:{{ $p['top'] }};left:{{ $p['left'] }};animation-delay:{{ $p['delay'] }}s;"></span>
                @endforeach

                <div class="hero-grid relative max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 h-full grid lg:grid-cols-[50%_50%] items-center gap-4 sm:gap-8">
                    <div class="hero-copy pt-2 sm:pt-8 lg:pt-0 order-2 lg:order-1">
                        {{-- Slide 1 sets the eyebrow in Instrument Serif; slide 2 uses the
                             mono uppercase treatment, per the copy deck. --}}
                        @if(($slide['eyebrowStyle'] ?? 'serif') === 'mono')
                            <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-3 sm:mb-4" data-reveal-child style="transition-delay:80ms">{{ $slide['eyebrow'] }}</p>
                        @else
                            <p class="font-edito italic text-lg sm:text-2xl text-pink mb-2 sm:mb-3" data-reveal-child style="transition-delay:80ms">{{ $slide['eyebrow'] }}</p>
                        @endif
                        <h1 class="font-display text-[2.75rem] leading-[1.02] sm:text-6xl lg:text-7xl text-ink mb-3 sm:mb-6" data-reveal-child style="transition-delay:160ms">
                            {{ $slide['heading'] }}
                        </h1>
                        <p class="text-base sm:text-lg text-ink-soft max-w-md mb-5 sm:mb-8" data-reveal-child style="transition-delay:240ms">
                            {{ $slide['subheading'] }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3 sm:gap-4" data-reveal-child style="transition-delay:320ms">
                            <x-button tag="a" href="{{ $slide['ctaHref'] }}" variant="primary" size="lg">{{ $slide['ctaLabel'] }}</x-button>
                            @if(!empty($slide['secondaryHref']))
                                <x-button tag="a" href="{{ $slide['secondaryHref'] }}" variant="secondary" size="lg">{{ $slide['secondaryLabel'] }}</x-button>
                            @endif
                        </div>

                        @if(!empty($slide['support']))
                            <p class="font-mono text-xs uppercase tracking-[0.08em] text-ink-soft mt-5 sm:mt-6" data-reveal-child style="transition-delay:400ms">
                                {{ $slide['support'] }}
                            </p>
                        @endif
                    </div>

                    <div class="hero-media relative order-1 lg:order-2 flex items-center justify-center">
                        @if($photo)
                            {{-- Below lg the shot cannot go full-bleed (far too wide for a
                                 portrait viewport), so it runs as a framed card cropped onto
                                 the product cluster instead. Mirror of the gate above: this
                                 one only downloads under 1024px, the full-bleed one only over. --}}
                            <picture class="lg:hidden w-full h-full">
                                <source media="(max-width: 1023px)" srcset="{{ $photoSrcset }}" sizes="100vw" type="image/webp">
                                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                     alt="" aria-hidden="true"
                                     @if($i === 0) fetchpriority="high" @endif
                                     class="w-full h-full object-cover rounded-card shadow-card"
                                     style="object-position: 72% 50%;">
                            </picture>
                        @endif

                        @unless($photo)
                        {{-- Anchoring spotlight --}}
                        <div class="hero-spotlight w-[85%] h-[85%]"></div>

                        {{-- Pedestal shadow --}}
                        <div class="absolute bottom-[6%] left-1/2 -translate-x-1/2 w-[80%] h-10 bg-ink/10 rounded-[50%] blur-lg"></div>

                        @foreach(($slide['images'] ?? []) as $j => $img)
                            <div class="hero-product-float absolute {{ $img['position'] }} {{ $img['rotate'] ?? '' }}"
                                 style="animation-delay: {{ $j * 0.7 }}s;">
                                <img src="{{ $img['src'] }}" alt="" width="{{ $img['w'] }}" height="{{ $img['h'] }}"
                                     class="{{ $img['size'] }} object-contain drop-shadow-[0_25px_35px_rgba(20,6,14,0.22)]"
                                     @if($i === 0 && $j === 0) fetchpriority="high" @else loading="lazy" @endif>
                            </div>
                        @endforeach
                        @endunless
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(count($slides) > 1)
        <div class="absolute bottom-5 sm:bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-2 z-10">
            @foreach($slides as $i => $slide)
                <button type="button" @click="goTo({{ $i }})"
                        class="slider-dot h-1.5 rounded-pill transition-all duration-300"
                        :class="active === {{ $i }} ? 'w-8 bg-pink' : 'w-1.5 bg-ink/20 hover:bg-ink/40'">
                    <span class="sr-only">Go to slide {{ $i + 1 }}</span>
                </button>
            @endforeach
        </div>

        <button type="button" @click="stop(); active = (active - 1 + count) % count; start()"
                class="hidden sm:flex absolute left-4 top-1/2 -translate-y-1/2 z-10 size-10 rounded-full bg-white/80 backdrop-blur items-center justify-center text-ink hover:bg-white transition">
            <span class="sr-only">Previous slide</span>
            <x-lucide-icon name="chevron-right" class="size-5 rotate-180" />
        </button>
        <button type="button" @click="next(); stop(); start()"
                class="hidden sm:flex absolute right-4 top-1/2 -translate-y-1/2 z-10 size-10 rounded-full bg-white/80 backdrop-blur items-center justify-center text-ink hover:bg-white transition">
            <span class="sr-only">Next slide</span>
            <x-lucide-icon name="chevron-right" class="size-5" />
        </button>
    @endif
</section>
