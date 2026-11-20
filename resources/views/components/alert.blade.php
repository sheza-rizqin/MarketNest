@props(['type' => 'success'])

@php
    $styles = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'error' => 'border-red-200 bg-red-50 text-red-900',
        'info' => 'border-blue-200 bg-blue-50 text-blue-900',
    ];
@endphp

<div role="status" {{ $attributes->class('rounded-xl border px-4 py-3 text-sm '.$styles[$type]) }}>
    {{ $slot }}
</div>
