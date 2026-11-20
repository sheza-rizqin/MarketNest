<x-app-layout :title="$title.' | MarketNest'">
    <section class="mx-auto max-w-7xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="$breadcrumbs" />

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Explore the marketplace</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">{{ $title }}</h1>
                <p class="mt-2 text-sm text-muted">{{ $listings->total() }} {{ \Illuminate\Support\Str::plural('active listing', $listings->total()) }}</p>
            </div>
            @auth
                <x-button :href="route('listings.create')">Create a listing</x-button>
            @else
                <x-button :href="route('register')">Start selling</x-button>
            @endauth
        </div>

        <div class="mt-7">
            <x-market-search :categories="$categories" :cities="$cities" />
        </div>

        @if ($category && $category->children->isNotEmpty())
            <div class="mt-7">
                <h2 class="text-sm font-semibold text-ink">Explore subcategories</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($category->children as $child)
                        @if ($city)
                            <a href="{{ route('cities.categories.show', [\Illuminate\Support\Str::slug($city), $category, $child]) }}" class="rounded-full border border-sand bg-white px-4 py-2 text-sm font-medium text-navy transition hover:border-navy/30">
                                {{ $child->name }} <span class="ml-1 text-muted">{{ $child->active_listings_count }}</span>
                            </a>
                        @else
                            <a href="{{ route('categories.show', [$category, $child]) }}" class="rounded-full border border-sand bg-white px-4 py-2 text-sm font-medium text-navy transition hover:border-navy/30">
                                {{ $child->name }} <span class="ml-1 text-muted">{{ $child->active_listings_count }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if ($listings->isNotEmpty())
            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($listings as $listing)
                    <x-product-card :listing="$listing" />
                @endforeach
            </div>
            <x-pagination :paginator="$listings" />
        @else
            <x-empty-state
                class="mt-8"
                title="No active listings here yet"
                description="Try another category or city, or check back when local sellers add new listings."
                action="Browse all categories"
                :href="route('categories.index')"
            />
        @endif
    </section>
</x-app-layout>
