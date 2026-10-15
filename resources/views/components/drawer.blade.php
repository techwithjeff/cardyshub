@props(['open' => 'open', 'side' => 'right', 'title' => null, 'widthClass' => 'max-w-md'])

{{--
    Base drawer. Expects an Alpine `open` boolean in scope (default prop name matches
    the local x-data key). Usage:
    <div x-data="{ open: false }">
        <x-drawer title="Your bag">...</x-drawer>
    </div>
--}}

<template x-teleport="body">
    <div x-show="{{ $open }}" x-cloak
         class="fixed inset-0 z-50"
         role="dialog" aria-modal="true" @if($title) aria-label="{{ $title }}" @endif>

        <div x-show="{{ $open }}" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="{{ $open }} = false" class="absolute inset-0 bg-ink/40"></div>

        <div x-show="{{ $open }}"
             x-trap.noscroll="{{ $open }}"
             @keydown.escape.window="{{ $open }} = false"
             x-transition:enter="transition ease-out duration-350 transform"
             x-transition:enter-start="{{ $side === 'right' ? 'translate-x-full' : '-translate-x-full' }}"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-250 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="{{ $side === 'right' ? 'translate-x-full' : '-translate-x-full' }}"
             class="absolute {{ $side === 'right' ? 'right-0' : 'left-0' }} top-0 h-full w-full {{ $widthClass }} bg-white shadow-drawer flex flex-col">

            @if($title)
                <div class="flex items-center justify-between px-5 py-4 border-b border-line shrink-0">
                    <h2 class="font-display text-lg text-ink">{{ $title }}</h2>
                    <button type="button" @click="{{ $open }} = false" class="text-ink-soft hover:text-ink">
                        <span class="sr-only">Close</span>
                        <x-lucide-icon name="x" class="size-5" />
                    </button>
                </div>
            @endif

            <div class="flex-1 overflow-y-auto">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="shrink-0 border-t border-line">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</template>
