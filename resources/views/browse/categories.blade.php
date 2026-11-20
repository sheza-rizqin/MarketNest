<x-app-layout title="All categories | MarketNest">
    <section class="mx-auto max-w-7xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'All categories']]" />

        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Find your corner of the marketplace</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">All categories</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Browse products and services by category, then narrow in on a subcategory.</p>
        </div>

        @forelse ($categories as $category)
            <section class="mt-10 first:mt-8">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-navy sm:text-2xl">
                            <a href="{{ route('categories.show', $category) }}" class="hover:text-orange">{{ $category->name }}</a>
                        </h2>
                        <p class="mt-1 text-sm text-muted">{{ $category->active_listings_count }} {{ \Illuminate\Support\Str::plural('active listing', $category->active_listings_count) }}</p>
                    </div>
                    <a href="{{ route('categories.show', $category) }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">Browse category</a>
                </div>

                @if ($category->children->isNotEmpty())
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($category->children as $subcategory)
                            <div>
                                <x-category-card :category="$subcategory" />
                                @if ($subcategory->children->isNotEmpty())
                                    <ul class="mt-2 flex flex-wrap gap-x-3 gap-y-1 px-2">
                                        @foreach ($subcategory->children as $nestedCategory)
                                            <li>
                                                <a href="{{ route('categories.show', [$subcategory, $nestedCategory]) }}" class="text-xs font-medium text-muted underline decoration-sand underline-offset-4 hover:text-navy hover:decoration-orange">
                                                    {{ $nestedCategory->name }} <span class="text-muted/80">({{ $nestedCategory->active_listings_count }})</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state class="mt-4" title="No subcategories yet" description="Browse this category to see its active listings." />
                @endif
            </section>
        @empty
            <x-empty-state class="mt-8" title="Categories are on their way" description="Categories will appear here as the marketplace grows." />
        @endforelse
    </section>
</x-app-layout>
