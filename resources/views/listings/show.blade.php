<x-app-layout :title="$listing->title.' | MarketNest'">
    <article class="mx-auto max-w-7xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="$breadcrumbs" />

        @if ($listing->status !== 'active')
            <x-alert type="info" class="mb-6">This listing is {{ $listing->status }} and is not visible to the public.</x-alert>
        @endif

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(19rem,.7fr)] lg:gap-12">
            <div class="grid gap-3 sm:grid-cols-2">
                @forelse ($listing->images->sortByDesc('is_primary') as $image)
                    <img
                        src="{{ route('listings.images.show', [$listing, $image]) }}"
                        alt="{{ $listing->title }}"
                        class="{{ $loop->first ? 'sm:col-span-2 aspect-[4/3] sm:aspect-[16/10]' : 'aspect-[4/3]' }} w-full rounded-2xl border border-sand bg-paper object-cover"
                    >
                @empty
                    <img src="{{ asset('images/listing-placeholder.svg') }}" alt="" class="sm:col-span-2 aspect-[4/3] w-full rounded-2xl border border-sand bg-paper object-cover">
                @endforelse
            </div>

            <div class="lg:sticky lg:top-28 lg:self-start">
                <p class="text-xs font-semibold uppercase tracking-[.13em] text-orange">
                    {{ $listing->category->name }}{{ $listing->subcategory ? ' / '.$listing->subcategory->name : '' }}
                </p>
                <h1 class="mt-3 text-3xl font-semibold leading-tight tracking-tight text-navy sm:text-4xl">{{ $listing->title }}</h1>
                <p class="mt-4 text-2xl font-bold tracking-tight text-navy">
                    @if (! is_null($listing->price))
                        ₹{{ number_format((float) $listing->price, 0) }}
                        <span class="ml-1 text-sm font-medium text-muted">{{ ucfirst($listing->price_type) }}</span>
                    @else
                        <span class="text-lg">Contact for price</span>
                    @endif
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 border-y border-sand py-4 text-sm text-muted">
                    <span>{{ $listing->area ? $listing->area.' · ' : '' }}{{ $listing->city }}, {{ $listing->state }}, {{ $listing->country }}</span>
                    <span class="text-sand" aria-hidden="true">|</span>
                    <span>{{ ucfirst($listing->listing_type) }}</span>
                    <span class="text-sand" aria-hidden="true">|</span>
                    <time datetime="{{ $listing->created_at->toDateString() }}">Posted {{ $listing->created_at->toFormattedDateString() }}</time>
                </div>

                <h2 class="mt-6 text-base font-semibold text-ink">About this {{ $listing->listing_type }}</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-secondary">{{ $listing->description }}</p>

                <div class="mt-6 rounded-2xl border border-sand bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Seller</p>
                    <p class="mt-1 text-base font-semibold text-navy">{{ $listing->user->name }}</p>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    @can('update', $listing)
                        <x-button :href="route('listings.edit', $listing)">Manage listing</x-button>
                    @else
                        @if ($listing->status === 'active')
                            <x-button href="#contact-seller" variant="accent">Contact seller</x-button>
                            <form method="POST" action="{{ route('cart.items.store', $listing) }}">
                                @csrf
                                <x-button type="submit">Add to cart</x-button>
                            </form>
                            @auth
                                <form method="POST" action="{{ route('listings.favorite', $listing) }}">
                                    @csrf
                                    <x-button type="submit" variant="outline">
                                        {{ auth()->user()->favoriteListings()->whereKey($listing->id)->exists() ? 'Remove saved listing' : 'Save listing' }}
                                    </x-button>
                                </form>
                            @endauth
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        @if ($listing->status === 'active' && ! auth()->user()?->is($listing->user))
            <section id="contact-seller" class="mt-12 grid gap-8 border-t border-sand pt-10 lg:grid-cols-[.8fr_1.2fr]">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Have a question?</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-navy">Contact {{ $listing->user->name }}</h2>
                    <p class="mt-3 text-sm leading-6 text-muted">Send the seller a message about this listing. Your contact details are shared with the seller only so they can reply.</p>
                </div>
                <form method="POST" action="{{ route('listings.contact', $listing) }}" class="rounded-2xl border border-sand bg-white p-5 sm:p-7">
                    @csrf
                    @if (auth()->guest())
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-form-input id="sender_name" name="sender_name" label="Your name" :value="old('sender_name')" required autocomplete="name" />
                            <x-form-input id="sender_email" name="sender_email" label="Email address" type="email" :value="old('sender_email')" required autocomplete="email" />
                        </div>
                    @endif
                    <div class="{{ auth()->guest() ? 'mt-4' : '' }}">
                        <label for="message" class="mb-1.5 block text-sm font-medium text-ink">Message</label>
                        <textarea id="message" name="message" rows="5" minlength="10" maxlength="5000" required class="block w-full rounded-xl border-sand bg-white px-3.5 text-sm text-ink placeholder:text-muted/70 focus:border-navy focus:ring-navy" placeholder="Ask a question or arrange a time to connect">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <x-button type="submit" variant="accent" class="mt-5">Send message</x-button>
                    @if ($errors->has('sender_name') || $errors->has('sender_email'))
                        <p class="mt-3 text-sm text-red-700">{{ $errors->first('sender_name') ?: $errors->first('sender_email') }}</p>
                    @endif
                </form>
            </section>
        @endif
    </article>

    @if ($relatedListings->isNotEmpty())
        <section class="border-t border-sand bg-white">
            <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-14">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Keep exploring</p>
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight text-navy">Related listings</h2>
                    </div>
                    <a href="{{ route('categories.show', $listing->subcategory ?? $listing->category) }}" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">Browse this category</a>
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedListings as $relatedListing)
                        <x-product-card :listing="$relatedListing" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-app-layout>
