@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'submit',
])

@php
    $styles = [
        'primary' => 'bg-navy text-white hover:bg-navy/90 focus-visible:ring-navy',
        'accent' => 'bg-orange text-white hover:bg-orange/90 focus-visible:ring-orange',
        'outline' => 'border border-sand bg-white text-navy hover:border-navy/30 focus-visible:ring-navy',
        'quiet' => 'text-navy hover:bg-paper focus-visible:ring-navy',
    ];
    $classes = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 '.$styles[$variant];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
