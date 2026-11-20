<x-app-layout title="Your cart | MarketNest">
    <section class="mx-auto max-w-7xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Cart'],
        ]" />

        <div class="mt-6">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Your cart</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">Review your requests</h1>
            <p class="mt-2 text-sm text-muted">One request per listing. Sellers confirm availability and any final pricing.</p>
        </div>

        @if ($removedMissingItems)
            <x-alert type="info" class="mt-6">A listing in your cart was removed because it is no longer available.</x-alert>
        @endif
        @if ($errors->has('cart'))
            <x-alert type="error" class="mt-6">{{ $errors->first('cart') }}</x-alert>
        @endif

        @if ($listingIds === [])
            <div class="mt-7">
                <x-empty-state
                    title="Your cart is empty"
                    description="Explore local products and services, then add a listing to start an order request."
                    action="Explore listings"
                    :href="route('listings.index')"
                />
            </div>
        @else
            <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(18rem,.7fr)]">
                <div class="space-y-3">
                    @foreach ($listingIds as $listingId)
                        @php($listing = $listings->get($listingId))
                        @if ($listing)
                            @php($image = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first())
                            @php($available = $listing->status === 'active' && auth()->id() !== $listing->user_id)
                            <article class="flex flex-col gap-4 rounded-2xl border border-sand bg-white p-4 sm:flex-row sm:items-center sm:p-5">
                                @if ($image && ($listing->status === 'active' || auth()->id() === $listing->user_id))
                                    <img src="{{ route('listings.images.show', [$listing, $image]) }}" alt="{{ $listing->title }}" class="aspect-[4/3] w-full rounded-xl bg-paper object-cover sm:h-28 sm:w-36">
                                @else
                                    <img src="{{ asset('images/listing-placeholder.svg') }}" alt="" class="aspect-[4/3] w-full rounded-xl bg-paper object-cover sm:h-28 sm:w-36">
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-orange">{{ ucfirst($listing->listing_type) }}</p>
                                    <h2 class="mt-1 break-words font-semibold text-navy">{{ $listing->title }}</h2>
                                    <p class="mt-1 text-sm text-muted">{{ $listing->city }}, {{ $listing->state }}</p>
                                    <p class="mt-2 text-sm font-semibold text-navy">
                                        @if ($listing->price_type === 'contact' || $listing->price === null)
                                            Price to be confirmed by seller
                                        @else
                                            ₹{{ number_format((float) $listing->price, 2) }}
                                            <span class="font-normal text-muted">· {{ ucfirst($listing->price_type) }}</span>
                                        @endif
                                    </p>
                                    @unless ($available)
                                        <p class="mt-2 text-sm font-medium text-red-700">
                                            {{ $listing->status !== 'active' ? 'This listing is no longer active.' : 'You cannot request your own listing.' }}
                                        </p>
                                    @endunless
                                </div>

                                <form method="POST" action="{{ route('cart.items.destroy', $listing->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="outline" class="w-full sm:w-auto">Remove</x-button>
                                </form>
                            </article>
                        @endif
                    @endforeach
                </div>

                <aside class="h-fit rounded-2xl border border-sand bg-white p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-navy">Order request</h2>
                    <div class="mt-4 flex justify-between gap-4 border-b border-sand pb-4 text-sm">
                        <span class="text-secondary">Listings</span>
                        <span class="font-semibold text-ink">{{ count($listingIds) }}</span>
                    </div>
                    <div class="mt-4 flex justify-between gap-4 text-sm">
                        <span class="text-secondary">Estimated total</span>
                        <span class="text-right font-semibold text-navy">
                            @if ($needsSellerQuote)
                                Confirmed by seller
                            @else
                                ₹{{ number_format((float) $subtotal, 2) }}
                            @endif
                        </span>
                    </div>
                    <p class="mt-4 text-xs leading-5 text-muted">No payment is taken and inventory is not reserved. Each seller will review their request and confirm availability, delivery, and final price.</p>

                    @if (! $hasUnavailableItems)
                        @auth
                            <form method="POST" action="{{ route('checkout.store') }}" class="mt-5">
                                @csrf
                                <label for="buyer_message" class="mb-1.5 block text-sm font-medium text-ink">Note to sellers <span class="font-normal text-muted">(optional)</span></label>
                                <textarea id="buyer_message" name="buyer_message" rows="3" maxlength="2000" class="block w-full rounded-xl border-sand bg-white px-3.5 text-sm text-ink focus:border-navy focus:ring-navy" placeholder="Add a question or preferred contact time">{{ old('buyer_message') }}</textarea>
                                @error('buyer_message') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                                <x-button type="submit" variant="accent" class="mt-4 w-full">Send order request</x-button>
                            </form>
                        @else
                            <x-button :href="route('login')" variant="accent" class="mt-5 w-full">Sign in to continue</x-button>
                        @endauth
                    @else
                        <p class="mt-5 rounded-xl bg-paper p-3 text-sm text-secondary">Remove unavailable listings before continuing.</p>
                    @endif
                </aside>
            </div>
        @endif
    </section>
</x-app-layout>
