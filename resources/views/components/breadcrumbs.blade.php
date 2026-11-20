@props(['items' => []])

<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex flex-wrap items-center gap-2 text-sm text-muted">
        @foreach ($items as $index => $item)
            @if ($index > 0)
                <li aria-hidden="true" class="text-sand">/</li>
            @endif
            <li>
                @if (! empty($item['url']) && $index < count($items) - 1)
                    <a href="{{ $item['url'] }}" class="transition hover:text-navy">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page" class="font-medium text-ink">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
