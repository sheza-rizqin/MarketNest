@props(['categories', 'cities'])

<form action="{{ route('listings.index') }}" method="GET" class="rounded-2xl border border-sand bg-white p-3 shadow-sm sm:p-4">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.5fr)_minmax(10rem,1fr)_minmax(10rem,1fr)]">
        <x-form-input
            id="search-q"
            name="q"
            label="Search listings"
            :value="request('q')"
            placeholder="Search title or description"
            class="!border-sand !bg-paper focus:!border-navy"
        />
        <x-form-select
            id="search-type"
            name="listing_type"
            label="Product or service"
            :options="collect(['product' => 'Products', 'service' => 'Services'])"
            :value="request('listing_type')"
            placeholder="All types"
        />
        <x-form-select
            id="search-city"
            name="city"
            label="City"
            :options="$cities->mapWithKeys(fn ($city) => [$city => $city])"
            :value="request('city')"
            placeholder="All cities"
        />
        <x-form-select
            id="search-category"
            name="category"
            label="Category"
            :options="$categories->mapWithKeys(fn ($category) => [$category->slug => ($category->parent ? $category->parent->name.' / ' : '').$category->name])"
            :value="request('category')"
            placeholder="All categories"
        />
        <div class="flex items-end">
            <x-button type="submit" class="w-full">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" stroke-width="1.8" />
                    <path d="m16 16 4 4" stroke-linecap="round" stroke-width="1.8" />
                </svg>
                Search
            </x-button>
        </div>
    </div>

    <details class="mt-4 border-t border-sand pt-3" @if (request()->except('page') !== []) open @endif>
        <summary class="cursor-pointer text-sm font-semibold text-navy">More filters</summary>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-form-select
                id="search-subcategory"
                name="subcategory"
                label="Subcategory"
                :options="$categories->filter(fn ($category) => $category->parent_id !== null)->mapWithKeys(fn ($category) => [$category->slug => ($category->parent?->name ? $category->parent->name.' / ' : '').$category->name])"
                :value="request('subcategory')"
                placeholder="All subcategories"
            />
            <x-form-input id="search-country" name="country" label="Country" :value="request('country')" placeholder="Any country" />
            <x-form-input id="search-state" name="state" label="State" :value="request('state')" placeholder="Any state" />
            <x-form-input id="search-area" name="area" label="Area" :value="request('area')" placeholder="Any area" />
            <x-form-input id="search-min-price" name="min_price" label="Minimum price" type="number" min="0" step="0.01" :value="request('min_price')" placeholder="No minimum" />
            <x-form-input id="search-max-price" name="max_price" label="Maximum price" type="number" min="0" step="0.01" :value="request('max_price')" placeholder="No maximum" />
            <x-form-select
                id="search-sort"
                name="sort"
                label="Sort by"
                :options="collect(['newest' => 'Newest first', 'oldest' => 'Oldest first', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low'])"
                :value="request('sort', 'newest')"
                placeholder="Choose sorting"
            />
        </div>
    </details>

    @if (request()->except('page') !== [])
        <div class="mt-3 flex justify-end">
            <a href="{{ route('listings.index') }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">Clear filters</a>
        </div>
    @endif
</form>
