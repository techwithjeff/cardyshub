@php
    $mergedAttributes = $attributes->merge(['class' => 'shrink-0'])->getAttributes();
    $attrString = collect($mergedAttributes)
        ->map(fn ($value, $key) => $value === true ? $key : sprintf('%s="%s"', $key, e($value)))
        ->implode(' ');
    $finalSvg = $svg ? preg_replace('/^<svg /', "<svg {$attrString} ", $svg, 1) : '';
@endphp
{!! $finalSvg !!}
