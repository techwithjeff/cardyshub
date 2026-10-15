@props(['message' => null, 'variant' => 'success'])

@php
    $icon = $variant === 'error' ? 'alert-circle' : 'check-circle-2';
    $iconColor = $variant === 'error' ? 'text-alert' : 'text-mint';
@endphp

<div x-data="{ show: {{ $message ? 'true' : 'false' }} }"
     x-show="show"
     x-cloak
     x-init="show && setTimeout(() => show = false, 4000)"
     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2" x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     role="status"
     class="fixed z-[60] top-4 inset-x-4 sm:inset-x-auto sm:top-auto sm:bottom-6 sm:right-6 flex items-center gap-3 rounded-card bg-white shadow-lift border border-line px-4 py-3 max-w-sm">
    <x-lucide-icon name="{{ $icon }}" class="size-5 shrink-0 {{ $iconColor }}" />
    <p class="text-sm text-ink">{{ $message }}</p>
    <button type="button" @click="show = false" class="ml-auto text-ink-faint hover:text-ink">
        <span class="sr-only">Dismiss</span>
        <x-lucide-icon name="x" class="size-4" />
    </button>
</div>
