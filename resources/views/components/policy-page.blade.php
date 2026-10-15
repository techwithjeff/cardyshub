@props([
    'title',
    'eyebrow' => null,
    'intro' => null,
    'lastUpdated' => false,
    'requires' => [],
    'toc' => [],
])

{{--
    Shared shell for Delivery, Returns, FAQs, Terms, Privacy and Cookies.

    `requires` lists the setting keys the page depends on. If any are still
    blank a draft banner appears at the top, so nobody publishes a legal page
    that is quietly missing its company name or returns address.
--}}
@php
    $missing = collect($requires)->reject(fn ($k) => \App\Models\Setting::filled($k))->values();
    $updated = trim((string) \App\Models\Setting::get('policy_last_updated', ''));
@endphp

<x-layouts.storefront :title="$title.' — Cardyshub'">

    <section class="about-hero border-b border-line">
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <nav aria-label="Breadcrumb" class="breadcrumb text-sm text-ink-soft mb-3">
                <a href="{{ route('home') }}" class="hover:text-pink">Home</a> &rsaquo; <span class="text-ink">{{ $title }}</span>
            </nav>
            @if($eyebrow)
                <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-3">{{ $eyebrow }}</p>
            @endif
            <h1 class="font-display text-4xl sm:text-5xl text-ink">{{ $title }}</h1>
            @if($intro)
                <p class="text-lg text-ink-soft mt-4 max-w-narrow">{{ $intro }}</p>
            @endif
            @if($lastUpdated)
                <p class="text-sm text-ink-faint mt-5">
                    Last updated:
                    @if($updated !== ''){{ $updated }}@else <mark class="policy-tbc">date to confirm</mark> @endif
                </p>
            @endif
        </div>
    </section>

    <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">

        @if($missing->isNotEmpty())
            <div class="policy-draft p-5 sm:p-6 mb-10 max-w-narrow">
                <p class="font-medium mb-2">This page is not ready to publish</p>
                <p class="text-sm mb-3">
                    {{ $missing->count() }} required {{ \Illuminate\Support\Str::plural('value', $missing->count()) }}
                    {{ $missing->count() === 1 ? 'is' : 'are' }} still outstanding. Highlighted markers below show where.
                </p>
                <ul class="text-sm list-disc list-inside">
                    @foreach($missing as $key)
                        <li><code>{{ $key }}</code></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid lg:grid-cols-[26%_74%] gap-10">
            @if(count($toc))
                <aside class="hidden lg:block">
                    <nav class="policy-toc" aria-label="On this page">
                        <p class="font-mono text-xs uppercase tracking-[0.08em] text-ink-faint mb-3">On this page</p>
                        <ul class="space-y-2 text-sm">
                            @foreach($toc as $id => $label)
                                <li><a href="#{{ $id }}" class="text-ink-soft hover:text-pink transition">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                </aside>
            @endif

            <div class="policy-prose max-w-narrow {{ count($toc) ? '' : 'lg:col-span-2' }}">
                {{ $slot }}
            </div>
        </div>
    </div>

</x-layouts.storefront>
