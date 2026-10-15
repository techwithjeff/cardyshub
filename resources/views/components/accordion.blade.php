@props(['items' => [], 'openFirst' => false])

<div x-data="{ open: {{ $openFirst ? 0 : 'null' }} }" class="divide-y divide-line border-t border-b border-line">
    @foreach($items as $i => $item)
        <div>
            <button type="button"
                    @click="open = (open === {{ $i }} ? null : {{ $i }})"
                    :aria-expanded="open === {{ $i }}"
                    aria-controls="accordion-panel-{{ $item['id'] ?? $i }}"
                    class="w-full flex items-center justify-between gap-4 py-4 text-left font-medium text-ink">
                <span>{{ $item['title'] }}</span>
                <x-lucide-icon name="chevron-down" class="size-5 shrink-0 text-ink-soft transition-transform duration-200" x-bind:class="{ 'rotate-180': open === {{ $i }} }" />
            </button>
            <div id="accordion-panel-{{ $item['id'] ?? $i }}"
                 x-show="open === {{ $i }}"
                 x-collapse
                 x-cloak
                 class="pb-4 text-sm text-ink-soft leading-relaxed">
                {!! $item['content'] !!}
            </div>
        </div>
    @endforeach
</div>
