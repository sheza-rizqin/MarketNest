<x-app-layout title="Seller dashboard | MarketNest">
    <section class="mx-auto max-w-7xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Seller dashboard']]" />

        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Seller dashboard</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="mt-2 text-sm text-muted">A clear view of your listings and what needs attention.</p>
            </div>
            <x-button href="{{ route('listings.create') }}">Create a listing</x-button>
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Total listings', 'value' => $statistics->total_listings, 'style' => 'text-navy'],
                ['label' => 'Active', 'value' => $statistics->active_listings, 'style' => 'text-emerald-700'],
                ['label' => 'Sold', 'value' => $statistics->sold_listings, 'style' => 'text-blue-700'],
                ['label' => 'Archived', 'value' => $statistics->archived_listings, 'style' => 'text-muted'],
            ] as $statistic)
                <div class="rounded-2xl border border-sand bg-white p-5">
                    <p class="text-sm font-medium text-secondary">{{ $statistic['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight {{ $statistic['style'] }}">{{ $statistic['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-8 grid gap-4 lg:grid-cols-[minmax(0,1.5fr)_minmax(16rem,1fr)]">
            <section class="min-w-0">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-navy">Recently created</h2>
                        <p class="mt-1 text-sm text-muted">Your five latest listings.</p>
                    </div>
                    <a href="{{ route('dashboard.listings') }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">View all listings</a>
                </div>

                @forelse ($recentListings as $listing)
                    <x-seller-listing-card :listing="$listing" :actions="false" class="mb-3 last:mb-0" />
                @empty
                    <x-empty-state
                        title="Your first listing starts here"
                        description="Create a product or service listing and manage it from this dashboard."
                        action="Create a listing"
                        :href="route('listings.create')"
                    />
                @endforelse
            </section>

            <aside class="rounded-2xl border border-sand bg-white p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-navy">Your MarketNest</h2>
                <p class="mt-1 text-sm text-muted">Shortcuts to the tools you use most.</p>
                <div class="mt-5 grid gap-2">
                    <a href="{{ route('dashboard.listings') }}" class="rounded-xl border border-sand px-4 py-3 text-sm font-semibold text-navy transition hover:border-navy/30 hover:bg-paper">Manage my listings</a>
                    <a href="{{ route('dashboard.enquiries') }}" class="rounded-xl border border-sand px-4 py-3 text-sm font-semibold text-navy transition hover:border-navy/30 hover:bg-paper">View messages</a>
                    <a href="{{ route('dashboard.orders') }}" class="rounded-xl border border-sand px-4 py-3 text-sm font-semibold text-navy transition hover:border-navy/30 hover:bg-paper">My order requests</a>
                    <a href="{{ route('dashboard.sales') }}" class="rounded-xl border border-sand px-4 py-3 text-sm font-semibold text-navy transition hover:border-navy/30 hover:bg-paper">Sales requests</a>
                    <a href="{{ route('favorites.index') }}" class="rounded-xl border border-sand px-4 py-3 text-sm font-semibold text-navy transition hover:border-navy/30 hover:bg-paper">View saved listings</a>
                    <a href="{{ route('profile.edit') }}" class="rounded-xl border border-sand px-4 py-3 text-sm font-semibold text-navy transition hover:border-navy/30 hover:bg-paper">View profile information</a>
                </div>
            </aside>
        </div>
    </section>
</x-app-layout>
