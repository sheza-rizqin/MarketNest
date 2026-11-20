@props(['category'])

<a href="{{ route('categories.show', $category) }}" class="group flex items-center justify-between gap-4 rounded-2xl border border-sand bg-white p-5 transition hover:border-navy/20 hover:shadow-md hover:shadow-navy/5">
    <span class="flex min-w-0 items-center gap-3.5">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-paper text-lg font-semibold text-navy" aria-hidden="true">{{ mb_substr($category->name, 0, 1) }}</span>
        <span class="min-w-0">
            <span class="block truncate font-semibold text-ink">{{ $category->name }}</span>
            <span class="mt-1 block text-xs text-muted">{{ $category->active_listings_count }} {{ \Illuminate\Support\Str::plural('listing', $category->active_listings_count) }}</span>
        </span>
    </span>
    <svg class="h-4 w-4 shrink-0 text-muted transition group-hover:translate-x-0.5 group-hover:text-orange" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
    </svg>
</a>
