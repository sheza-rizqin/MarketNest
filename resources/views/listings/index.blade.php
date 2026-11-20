<x-app-layout title="Browse local listings | MarketNest">
    <section class="mx-auto max-w-7xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Explore listings']]" />

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">The neighborhood marketplace</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">Find something good nearby</h1>
                <p class="mt-2 text-sm text-muted">{{ $listings->total() }} {{ \Illuminate\Support\Str::plural('active listing', $listings->total()) }} to explore</p>
            </div>
            @auth
                <x-button href="{{ route('listings.create') }}">Create a listing</x-button>
            @else
                <x-button href="{{ route('register') }}">Start selling</x-button>
            @endauth
        </div>

        <div class="mt-7">
            <x-market-search :categories="$categories" :cities="$cities" />
        </div>

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
                title="No listings match that search"
                description="Try a different keyword, category, or location to find what you’re looking for."
                action="Clear filters"
                :href="route('listings.index')"
            />
        @endif
    </section>
</x-app-layout>
