@props(['label', 'name', 'type' => 'text', 'error' => null, 'required' => false])

@php
    $id = 'field-'.$name;
    $hasError = (bool) $error;
@endphp

<div>
    <div class="relative">
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
               placeholder=" "
               {{ $attributes->merge([
                   'class' => 'peer w-full min-h-[44px] rounded border px-3.5 pt-5 pb-1.5 text-sm text-ink placeholder-transparent focus:outline-none focus:ring-1 '
                       . ($hasError ? 'border-alert focus:border-alert focus:ring-alert' : 'border-line focus:border-pink focus:ring-pink'),
               ]) }}
               @if($required) required aria-required="true" @endif
               @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
        <label for="{{ $id }}"
               class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-soft text-sm transition-all
                      peer-focus:top-3.5 peer-focus:text-xs peer-focus:-translate-y-0 peer-focus:text-pink
                      peer-[:not(:placeholder-shown)]:top-3.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:-translate-y-0">
            {{ $label }}
        </label>
    </div>
    @if($hasError)
        <p id="{{ $id }}-error" class="mt-1.5 text-xs text-alert">{{ $error }}</p>
    @endif
</div>
