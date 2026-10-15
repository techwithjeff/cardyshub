@props(['label', 'name', 'error' => null])

@php $id = 'field-'.$name; @endphp

<div>
    <label for="{{ $id }}" class="flex items-start gap-2.5 text-sm text-ink cursor-pointer">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1"
               {{ $attributes->merge(['class' => 'mt-0.5 size-4 rounded border-line text-pink-btn focus:ring-pink']) }}>
        <span>{{ $label }}</span>
    </label>
    @if($error)
        <p class="mt-1.5 text-xs text-alert">{{ $error }}</p>
    @endif
</div>
