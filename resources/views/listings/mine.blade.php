<x-app-layout title="My listings | MarketNest">
    <section class="mx-auto max-w-7xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Seller dashboard', 'url' => route('dashboard')], ['label' => 'My listings']]" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Seller workspace</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">My listings</h1>
                <p class="mt-2 text-sm text-muted">{{ $listings->total() }} {{ \Illuminate\Support\Str::plural('listing', $listings->total()) }} created by you.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-button href="{{ route('dashboard') }}" variant="outline">Dashboard overview</x-button>
                <x-button href="{{ route('listings.create') }}">Create a listing</x-button>
            </div>
        </div>

        @if ($errors->any())
            <x-alert type="error" class="mt-6">{{ $errors->first() }}</x-alert>
        @endif

        <div class="mt-7 space-y-3">
            @forelse ($listings as $listing)
                <x-seller-listing-card :listing="$listing" />
            @empty
                <x-empty-state
                    title="No listings yet"
                    description="Create your first product or service listing to start selling on MarketNest."
                    action="Create a listing"
                    :href="route('listings.create')"
                />
            @endforelse
        </div>

        <x-pagination :paginator="$listings" />
    </section>
</x-app-layout>
