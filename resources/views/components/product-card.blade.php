@props(['listing'])

@php
    $primaryImage = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first();
    $isFavorited = (bool) ($listing->is_favorited ?? false);
@endphp

<article class="group relative overflow-hidden rounded-2xl border border-sand bg-white transition duration-200 hover:-translate-y-0.5 hover:border-navy/20 hover:shadow-lg hover:shadow-navy/5">
    <a href="{{ route('listings.show', $listing) }}" class="block">
        <div class="relative aspect-[4/3] overflow-hidden bg-paper">
            @if ($primaryImage)
                <img
                    src="{{ route('listings.images.show', [$listing, $primaryImage]) }}"
                    alt="{{ $listing->title }}"
                    loading="lazy"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                >
            @else
                <img src="{{ asset('images/listing-placeholder.svg') }}" alt="" loading="lazy" class="h-full w-full object-cover">
            @endif
            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-navy">
                {{ ucfirst($listing->listing_type) }}
            </span>
        </div>
        <div class="p-4">
            <p class="truncate text-xs font-medium text-muted">
                {{ $listing->subcategory ? $listing->category->name.' / '.$listing->subcategory->name : $listing->category->name }}
            </p>
            <h3 class="mt-1.5 line-clamp-2 min-h-12 text-base font-semibold leading-6 text-ink">{{ $listing->title }}</h3>
            <div class="mt-3 flex items-baseline gap-2">
                @if (! is_null($listing->price))
                    <span class="text-lg font-bold tracking-tight text-navy">₹{{ number_format((float) $listing->price, 0) }}</span>
                @else
                    <span class="text-base font-semibold text-navy">Contact for price</span>
                @endif
                @if ($listing->price_type !== 'contact')
                    <span class="text-xs text-muted">{{ ucfirst($listing->price_type) }}</span>
                @endif
            </div>
            <p class="mt-3 truncate text-sm text-secondary">
                {{ $listing->area ? $listing->area.' · ' : '' }}{{ $listing->city }}
            </p>
            <p class="mt-1 text-xs text-muted">Posted {{ $listing->created_at->diffForHumans() }}</p>
        </div>
    </a>

    <div class="absolute right-3 top-3">
        @auth
            <form method="POST" action="{{ route('listings.favorite', $listing) }}">
                @csrf
                <button type="submit" aria-label="{{ $isFavorited ? 'Remove from saved listings' : 'Save listing' }}" aria-pressed="{{ $isFavorited ? 'true' : 'false' }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-navy shadow-sm transition hover:text-orange focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" aria-hidden="true">
                        <path d="M20.8 8.6c0 5.2-8.8 10.1-8.8 10.1S3.2 13.8 3.2 8.6A4.6 4.6 0 0 1 12 6.4a4.6 4.6 0 0 1 8.8 2.2Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" />
                    </svg>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" aria-label="Sign in to save listing" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-navy shadow-sm transition hover:text-orange">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path d="M20.8 8.6c0 5.2-8.8 10.1-8.8 10.1S3.2 13.8 3.2 8.6A4.6 4.6 0 0 1 12 6.4a4.6 4.6 0 0 1 8.8 2.2Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" />
                </svg>
            </a>
        @endauth
    </div>
</article>
