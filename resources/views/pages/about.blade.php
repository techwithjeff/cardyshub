<x-layouts.storefront :title="'About Us — Cardyshub'">

    {{-- SECTION 1: Page hero --}}
    <section class="about-hero border-b border-line">
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24" data-reveal-section>
            <div class="max-w-narrow">
                <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-4" data-reveal-child>Our story</p>
                <h1 class="font-display text-4xl sm:text-5xl text-ink mb-5" data-reveal-child>Built by someone who needed it first</h1>
                <p class="text-lg text-ink-soft" data-reveal-child>
                    Cardyshub started with one person, a bathroom shelf full of products that did not work,
                    and a decision to make it easier for everyone who comes next.
                </p>
            </div>
        </div>
    </section>

    {{-- SECTION 2: The brand --}}
    <section class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24" data-reveal-section>
        <div class="max-w-narrow">
            <h2 class="font-display text-3xl sm:text-4xl text-ink mb-6" data-reveal-child>Skincare, without the guesswork</h2>

            <div class="space-y-5 text-ink-soft leading-relaxed" data-reveal-child>
                <p>Most people do not give up on skincare because they stop caring. They give up because nobody told them what they were doing.</p>
                <p>You buy the serum everyone is talking about. You use it for a week. Nothing obvious happens, so you buy something else, and layer it on top of the first thing, and now your skin is irritated and you are back where you started, just poorer. That cycle is exhausting, and it is the reason so many people decide their skin is simply difficult.</p>
                <p class="font-edito italic text-2xl text-ink">Cardyshub was built to break that cycle.</p>
                <p>We launched in March 2025 with a simple idea: that good skincare is less about finding a miracle product and more about knowing what to use, in what order, and why. So every product we sell comes with the part that usually gets left out. What it does. When to use it. What goes on before it and after it. Written in plain English, not marketing language.</p>
            </div>
        </div>

        {{-- What we believe --}}
        <div class="mt-12 sm:mt-20">
            <h3 class="font-display text-2xl text-ink mb-8" data-reveal-child>What we believe</h3>

            <div class="grid sm:grid-cols-2 gap-4 sm:gap-6">
                @foreach([
                    ['n' => '01', 'title' => 'Your skin is not a problem to be fixed', 'copy' => 'The industry sells anxiety first and solutions second. We are not interested in that. Your skin is a working organ doing its job, and the goal is to support it, not punish it into submission.'],
                    ['n' => '02', 'title' => 'Fewer products, used properly, beat more products used randomly', 'copy' => 'A four step routine you actually follow will do more than a twelve step routine you follow for a fortnight. We would rather sell you three products that suit you than eight that sit unopened.'],
                    ['n' => '03', 'title' => 'You deserve to know what you are putting on your face', 'copy' => 'Full ingredient lists on every product page. Honest descriptions of what a formula does and what it does not. If something is not right for your skin type, we say so, even when that costs us the sale.'],
                    ['n' => '04', 'title' => 'Confidence is the actual point', 'copy' => 'Nobody buys a moisturiser because they want a moisturiser. They buy it because of how they want to feel walking into a room. We have never forgotten that.'],
                ] as $belief)
                    <div class="belief-card bg-white border border-line rounded-card p-6 sm:p-8 hover:border-pink transition-colors duration-300" data-reveal-child>
                        <span class="belief-number font-display" aria-hidden="true">{{ $belief['n'] }}</span>
                        <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-3">{{ $belief['n'] }}</p>
                        <h4 class="font-display text-xl text-ink mb-3">{{ $belief['title'] }}</h4>
                        <p class="text-sm text-ink-soft leading-relaxed">{{ $belief['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- SECTION 3: Meet the founder --}}
    <section class="founder-band border-y border-line" data-reveal-section>
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24">

            <div class="grid lg:grid-cols-2 gap-10 lg:gap-10 items-center">

                {{-- Portrait: arch crop, offset outline behind, quote card breaking out --}}
                <div data-reveal-child>
                    <figure class="founder-figure">
                        <span class="founder-badge font-mono">Est. March 2025</span>
                        <img src="{{ asset('storage/'.$founderPhoto) }}" alt="{{ $founderName }}, founder of Cardyshub"
                             class="founder-photo" width="640" height="800">
                        <figcaption class="founder-quote">
                            <p class="font-edito italic text-xl sm:text-2xl text-ink leading-tight">
                                Skincare is more than just products. It is about feeling comfortable in the skin you are in.
                            </p>
                        </figcaption>
                    </figure>
                </div>

                {{-- Copy --}}
                <div data-reveal-child>
                    <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-4">Meet the founder</p>
                    <h2 class="font-display text-3xl sm:text-4xl text-ink mb-2">{{ $founderName }}</h2>
                    <p class="text-sm text-ink-faint mb-8">Founder, Cardyshub.uk &middot; Business Administration, University of Lagos</p>

                    <div class="space-y-4 text-ink-soft leading-relaxed">
                        <p>My skincare journey began with my own experience. After years of trying different products through plenty of trial and error, I discovered just how much the right skincare products could transform not only my skin, but also the way I felt about myself. That experience inspired me to create something that could help others on their own skincare journeys.</p>
                        <p>I launched Cardyshub.uk in March 2025, with so much love, passion and a genuine desire to help people feel better and more confident in their skin.</p>
                        <p>I believe skincare is more than just products. It is about self care, confidence and feeling comfortable in the skin you are in. Cardyshub.uk was created to make that journey a little easier, more enjoyable and more empowering.</p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-line">
                        <p class="founder-signature">Cardy</p>
                        <p class="font-mono text-xs uppercase tracking-[0.08em] text-ink-faint mt-1">Founder</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 4: How we work --}}
    <section id="how-we-work" class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24" data-reveal-section>
        <x-section-heading eyebrow="How we work" align="center">What you can count on</x-section-heading>

        <div class="grid sm:grid-cols-3 gap-8 sm:gap-6">
            @foreach([
                ['icon' => 'heart-handshake', 'title' => 'Made from experience', 'copy' => 'Every product exists because the founder needed it first and could not find it done properly. We build what we would use.'],
                ['icon' => 'file-text', 'title' => 'Ingredients up front', 'copy' => 'Full ingredient lists on every product page, and an honest description of what a formula does and what it does not.'],
                ['icon' => 'message-circle', 'title' => 'Honest advice', 'copy' => 'We tell you what a product does and what it will not do. No miracle claims, no before and after photos that were taken under different lighting.'],
            ] as $value)
                <div class="text-center" data-reveal-child>
                    <div class="size-14 rounded-full bg-pink-wash flex items-center justify-center mx-auto mb-5">
                        <x-lucide-icon name="{{ $value['icon'] }}" class="size-6 text-pink" />
                    </div>
                    <h3 class="font-display text-xl text-ink mb-3">{{ $value['title'] }}</h3>
                    <p class="text-sm text-ink-soft leading-relaxed">{{ $value['copy'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- SECTION 5: Closing call to action --}}
    <section class="newsletter-gradient" data-reveal-section>
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
            <h2 class="font-display text-3xl sm:text-4xl text-white mb-3" data-reveal-child>Ready when you are</h2>
            <p class="text-white/80 max-w-md mx-auto mb-8" data-reveal-child>
                Start with one product or build the whole routine. Either way, we will tell you exactly how to use it.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4" data-reveal-child>
                <x-button tag="a" href="{{ route('shop.index') }}" variant="pill" size="lg" class="!bg-ink hover:!bg-ink/80">Shop all products</x-button>
                <x-button tag="a" href="{{ route('home') }}#concerns" variant="pill" size="lg" class="btn-on-dark">Find your routine</x-button>
            </div>
        </div>
    </section>

</x-layouts.storefront>
