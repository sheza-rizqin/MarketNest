@props(['listing', 'actions' => true])

@php
    $primaryImage = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first();
    $statusClasses = [
        'active' => 'bg-emerald-50 text-emerald-800',
        'sold' => 'bg-blue-50 text-blue-800',
        'archived' => 'bg-paper text-muted',
    ];
@endphp

<article {{ $attributes->class('rounded-2xl border border-sand bg-white p-4 sm:p-5') }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex min-w-0 gap-4">
            <a href="{{ route('listings.show', $listing) }}" class="h-24 w-28 shrink-0 overflow-hidden rounded-xl bg-paper sm:h-28 sm:w-36">
                @if ($primaryImage)
                    <img src="{{ route('listings.images.show', [$listing, $primaryImage]) }}" alt="{{ $listing->title }}" class="h-full w-full object-cover">
                @else
                    <img src="{{ asset('images/listing-placeholder.svg') }}" alt="" class="h-full w-full object-cover">
                @endif
            </a>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="truncate text-base font-semibold text-navy sm:text-lg">{{ $listing->title }}</h2>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $statusClasses[$listing->status] }}">
                        {{ $listing->status }}
                    </span>
                </div>
                <p class="mt-1 truncate text-sm text-muted">
                    {{ $listing->category->name }}{{ $listing->subcategory ? ' / '.$listing->subcategory->name : '' }}
                </p>
                <p class="mt-2 text-sm font-semibold text-navy">
                    @if ($listing->price !== null)
                        ₹{{ number_format((float) $listing->price, 0) }}
                        <span class="font-normal text-muted">· {{ ucfirst($listing->price_type) }}</span>
                    @else
                        Contact for price
                    @endif
                </p>
                <p class="mt-1 text-sm text-secondary">{{ $listing->city }}</p>
                <p class="mt-1 text-xs text-muted">Created {{ $listing->created_at->format('M j, Y') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
            @if ($actions)
                <a href="{{ route('listings.show', $listing) }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">View</a>
                <a href="{{ route('listings.edit', $listing) }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">Edit</a>

                <form method="POST" action="{{ route('listings.status.update', $listing) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <label class="sr-only" for="status-{{ $listing->id }}">Change status for {{ $listing->title }}</label>
                    <select id="status-{{ $listing->id }}" name="status" class="min-h-10 rounded-lg border-sand py-2 text-sm">
                        @foreach (['active' => 'Active', 'sold' => 'Sold', 'archived' => 'Archived'] as $value => $label)
                            <option value="{{ $value }}" @selected($listing->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">Update</button>
                </form>

                <form method="POST" action="{{ route('listings.destroy', $listing) }}" onsubmit="return confirm('Delete this listing and its photos?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-semibold text-red-700 underline decoration-red-200 underline-offset-4 hover:decoration-red-500">Delete</button>
                </form>
            @else
                <a href="{{ route('listings.show', $listing) }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">View</a>
                <a href="{{ route('listings.edit', $listing) }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">Edit</a>
            @endif
        </div>
    </div>
</article>
