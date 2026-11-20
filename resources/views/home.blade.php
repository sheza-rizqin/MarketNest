<x-app-layout title="MarketNest | Discover locally. Buy confidently. Sell easily.">
    <section class="relative overflow-hidden border-b border-sand bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 py-12 sm:px-8 sm:py-16 lg:grid-cols-[1.05fr_.95fr] lg:items-center lg:gap-14 lg:py-20">
            <div class="relative z-10">
                <p class="inline-flex items-center gap-2 rounded-full border border-sand bg-paper px-3.5 py-2 text-xs font-semibold uppercase tracking-[.13em] text-navy">
                    <span class="h-2 w-2 rounded-full bg-orange"></span>
                    Your neighborhood, better connected
                </p>
                <h1 class="mt-6 max-w-2xl text-4xl font-semibold leading-[1.08] tracking-tight text-navy sm:text-5xl lg:text-[3.6rem]">
                    Find your next good thing, <span class="text-orange">just around the corner.</span>
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-muted sm:text-lg">
                    {{ config('app.name', 'MarketNest') }} connects you with useful finds and local services from people in your community.
                </p>
                <div class="mt-8 max-w-3xl">
                    <x-market-search :categories="$categories" :cities="$cities" />
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-muted">
                    <span class="font-medium text-ink">A better local marketplace</span>
                    <span class="hidden h-1 w-1 rounded-full bg-sand sm:block"></span>
                    <a href="{{ route('listings.index') }}" class="font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">Browse all listings</a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-xl lg:ml-auto">
                <div class="absolute -right-7 -top-5 h-28 w-28 rounded-full bg-orange/10"></div>
                <div class="absolute -bottom-7 -left-6 h-32 w-32 rounded-full bg-navy/5"></div>
                <div class="relative overflow-hidden rounded-[1.75rem] border border-sand bg-paper p-3 shadow-xl shadow-navy/5">
                    @if ($featuredListing)
                        @php($heroImage = $featuredListing->images->firstWhere('is_primary', true) ?? $featuredListing->images->first())
                        <a href="{{ route('listings.show', $featuredListing) }}" class="group block">
                            <div class="relative overflow-hidden rounded-[1.25rem] bg-white">
                                @if ($heroImage)
                                    <img src="{{ route('listings.images.show', [$featuredListing, $heroImage]) }}" alt="{{ $featuredListing->title }}" class="h-[20rem] w-full object-cover transition duration-300 group-hover:scale-[1.02] sm:h-[25rem]">
                                @else
                                    <img src="{{ asset('images/listing-placeholder.svg') }}" alt="" class="h-[20rem] w-full object-cover sm:h-[25rem]">
                                @endif
                                <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold text-navy">Recently added</span>
                            </div>
                            <div class="flex items-center justify-between gap-4 px-2 py-4">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-medium text-muted">{{ $featuredListing->category->name }} · {{ $featuredListing->city }}</p>
                                    <h2 class="mt-1 truncate font-semibold text-ink">{{ $featuredListing->title }}</h2>
                                </div>
                                <span class="shrink-0 text-lg font-bold text-navy">
                                    {{ ! is_null($featuredListing->price) ? '₹'.number_format((float) $featuredListing->price, 0) : 'Contact' }}
                                </span>
                            </div>
                        </a>
                    @else
                        <div class="overflow-hidden rounded-[1.25rem] bg-white">
                            <img src="{{ asset('images/listing-placeholder.svg') }}" alt="" class="h-[20rem] w-full object-cover sm:h-[25rem]">
                            <div class="px-5 py-4">
                                <p class="font-semibold text-navy">Your next find starts here</p>
                                <p class="mt-1 text-sm text-muted">Explore a growing community marketplace.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section id="categories" class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-16">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">A little bit of everything</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-navy sm:text-3xl">Explore popular categories</h2>
                <p class="mt-2 text-sm text-muted">See what people are sharing in your community.</p>
            </div>
            <a href="{{ route('listings.index') }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">View all listings</a>
        </div>

        @if ($categories->isNotEmpty())
            <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <x-category-card :category="$category" />
                @endforeach
            </div>
        @else
            <x-empty-state class="mt-7" title="Categories are on their way" description="Once sellers publish active listings, you’ll find them organized here." />
        @endif
    </section>

    <section class="border-y border-sand bg-white">
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-16">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Fresh from the community</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-navy sm:text-3xl">Recently added</h2>
                    <p class="mt-2 text-sm text-muted">New finds and local services, posted by real sellers.</p>
                </div>
                <a href="{{ route('listings.index') }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">See all listings</a>
            </div>

            @if ($recentListings->isNotEmpty())
                <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($recentListings as $listing)
                        <x-product-card :listing="$listing" />
                    @endforeach
                </div>
            @else
                <x-empty-state class="mt-7" title="The first finds are waiting" description="Be among the first to share something useful with your neighborhood." action="Create a listing" :href="auth()->check() ? route('listings.create') : route('register')" />
            @endif
        </div>
    </section>

    <section id="cities" class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-16">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Close to home</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-navy sm:text-3xl">Browse your city</h2>
            <p class="mt-2 text-sm text-muted">Local listings in places where neighbors make a difference.</p>
        </div>

        @if ($popularCities->isNotEmpty())
            <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($popularCities as $city)
                    <a href="{{ route('cities.show', \Illuminate\Support\Str::slug($city->city)) }}" class="group flex items-center justify-between rounded-2xl border border-sand bg-white px-5 py-4 transition hover:border-navy/20 hover:shadow-md hover:shadow-navy/5">
                        <span>
                            <span class="block font-semibold text-ink">{{ $city->city }}</span>
                            <span class="mt-1 block text-xs text-muted">{{ $city->listings_count }} {{ \Illuminate\Support\Str::plural('active listing', $city->listings_count) }}</span>
                        </span>
                        <svg class="h-4 w-4 text-muted transition group-hover:translate-x-0.5 group-hover:text-orange" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                        </svg>
                    </a>
                @endforeach
            </div>
        @else
            <x-empty-state class="mt-7" title="Your city is next" description="Active listings will appear here as the community grows." />
        @endif
    </section>

    <section class="px-5 pb-14 sm:px-8 sm:pb-16">
        <div class="mx-auto max-w-7xl overflow-hidden rounded-[1.75rem] bg-navy px-6 py-10 text-white sm:px-10 sm:py-12 lg:flex lg:items-center lg:justify-between lg:gap-10">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">For your neighborhood</p>
                <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Have something good to pass along?</h2>
                <p class="mt-3 text-sm leading-6 text-white/70 sm:text-base">Create a listing and help someone nearby find just what they need.</p>
            </div>
            <div class="mt-6 shrink-0 lg:mt-0">
                @auth
                    <x-button href="{{ route('listings.create') }}" variant="accent">Create a listing</x-button>
                @else
                    <x-button href="{{ route('register') }}" variant="accent">Start selling</x-button>
                @endauth
            </div>
        </div>
    </section>
</x-app-layout>
