@props(['key' => null, 'label' => 'to confirm', 'prefix' => '', 'suffix' => ''])

{{--
    Renders a client-supplied policy value, or a visible marker when it is still
    blank. These pages carry legal weight, so an unfilled figure must never be
    silently invented or silently dropped — it has to be obvious that something
    is outstanding. `x-policy-value` is deliberately loud for that reason.
--}}
@php
    $value = trim((string) \App\Models\Setting::get($key, ''));
@endphp

@if($value !== '')
    {{ $prefix }}{{ $value }}{{ $suffix }}
@else
    <mark class="policy-tbc" title="This value has not been supplied yet">{{ $label }}</mark>
@endif
