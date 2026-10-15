@props(['messages' => []])

<div class="bg-ink text-bone overflow-hidden py-2.5" aria-hidden="true">
    <div class="flex whitespace-nowrap animate-marquee">
        @for($i = 0; $i < 2; $i++)
            <div class="flex shrink-0">
                @foreach($messages as $message)
                    <span class="font-mono text-xs uppercase tracking-[0.1em] px-6">{{ $message }}</span>
                    <span class="text-pink px-1" aria-hidden="true">&middot;</span>
                @endforeach
            </div>
        @endfor
    </div>
</div>
