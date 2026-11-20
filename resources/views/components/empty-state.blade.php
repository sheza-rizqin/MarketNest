@props(['title', 'description', 'action' => null, 'href' => null])

<div {{ $attributes->class('rounded-2xl border border-dashed border-sand bg-white px-6 py-12 text-center') }}>
    <h2 class="text-lg font-semibold text-navy">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">{{ $description }}</p>
    @if ($action && $href)
        <x-button :href="$href" variant="accent" class="mt-5">{{ $action }}</x-button>
    @endif
</div>
