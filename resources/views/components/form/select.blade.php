@props(['label', 'name', 'options' => [], 'error' => null, 'required' => false])

@php
    $id = 'field-'.$name;
    $hasError = (bool) $error;
@endphp

<div>
    <div class="relative">
        <select id="{{ $id }}" name="{{ $name }}"
                {{ $attributes->merge([
                    'class' => 'peer w-full min-h-[44px] rounded border px-3.5 pt-5 pb-1.5 text-sm text-ink focus:outline-none focus:ring-1 appearance-none bg-white '
                        . ($hasError ? 'border-alert focus:border-alert focus:ring-alert' : 'border-line focus:border-pink focus:ring-pink'),
                ]) }}
                @if($required) required aria-required="true" @endif
                @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
            @foreach($options as $value => $text)
                <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
        </select>
        <label for="{{ $id }}" class="absolute left-3.5 top-2 text-xs text-ink-soft">{{ $label }}</label>
        <x-lucide-icon name="chevron-down" class="size-4 text-ink-soft absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
    </div>
    @if($hasError)
        <p id="{{ $id }}-error" class="mt-1.5 text-xs text-alert">{{ $error }}</p>
    @endif
</div>
