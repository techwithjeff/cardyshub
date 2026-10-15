@props(['name' => 'quantity', 'value' => 1, 'min' => 1, 'max' => 20])

<div x-data="{ qty: {{ (int) $value }}, min: {{ (int) $min }}, max: {{ (int) $max }} }"
     class="qty-control inline-flex items-center border border-line h-11">
    <button type="button" @click="qty = Math.max(min, qty - 1)"
            class="w-11 h-full flex items-center justify-center text-ink-soft hover:text-ink disabled:opacity-30"
            :disabled="qty <= min">
        <span class="sr-only">Decrease quantity</span>
        <x-lucide-icon name="minus" class="size-4" />
    </button>
    <input type="number" name="{{ $name }}" x-model.number="qty" :min="min" :max="max"
           class="w-12 h-full border-0 text-center font-mono text-sm focus:ring-0 p-0" />
    <button type="button" @click="qty = Math.min(max, qty + 1)"
            class="w-11 h-full flex items-center justify-center text-ink-soft hover:text-ink disabled:opacity-30"
            :disabled="qty >= max">
        <span class="sr-only">Increase quantity</span>
        <x-lucide-icon name="plus" class="size-4" />
    </button>
</div>
