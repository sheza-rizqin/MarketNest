<x-app-layout title="Saved listings | MarketNest">
    <section class="mx-auto max-w-7xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Saved listings']]" />

        <div>
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Your shortlist</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">Saved listings</h1>
            <p class="mt-2 text-sm text-muted">{{ $listings->total() }} {{ \Illuminate\Support\Str::plural('active listing', $listings->total()) }} saved</p>
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
                title="No saved listings yet"
                description="Save listings you like and they’ll be easy to find here."
                action="Explore listings"
                :href="route('listings.index')"
            />
        @endif
    </section>
</x-app-layout>
