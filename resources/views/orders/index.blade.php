<x-app-layout title="My order requests | MarketNest">
    <section class="mx-auto max-w-5xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Seller dashboard', 'url' => route('dashboard')],
            ['label' => 'My order requests'],
        ]" />

        <div class="mt-6">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Buyer activity</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">My order requests</h1>
            <p class="mt-2 text-sm text-muted">Track each seller’s response. These requests do not take payment.</p>
        </div>

        <div class="mt-7 space-y-4">
            @forelse ($orders as $order)
                <article class="rounded-2xl border border-sand bg-white p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Request #{{ $order->id }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-navy">Seller: {{ $order->seller->name }}</h2>
                            <p class="mt-1 text-sm text-muted">Placed {{ $order->created_at->toFormattedDateString() }}</p>
                        </div>
                        <span class="rounded-full bg-paper px-3 py-1 text-sm font-semibold capitalize text-navy">{{ $order->status }}</span>
                    </div>

                    <ul class="mt-4 divide-y divide-sand border-y border-sand">
                        @foreach ($order->items as $item)
                            <li class="flex flex-wrap justify-between gap-2 py-3 text-sm">
                                <span class="font-medium text-ink">{{ $item->listing_title }} <span class="font-normal text-muted">· {{ ucfirst($item->listing_type) }}</span></span>
                                <span class="text-secondary">
                                    @if ($item->price_type === 'contact' || $item->listed_price === null)
                                        Seller to confirm price
                                    @else
                                        ₹{{ number_format((float) $item->listed_price, 2) }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-navy">
                            @if ($order->amount_due === null)
                                Total to be confirmed by seller
                            @else
                                Listed total: ₹{{ number_format((float) $order->amount_due, 2) }}
                            @endif
                        </p>
                        @if ($order->status === 'pending')
                            <form method="POST" action="{{ route('dashboard.orders.cancel', $order) }}">
                                @csrf
                                @method('PATCH')
                                <x-button type="submit" variant="outline">Cancel request</x-button>
                            </form>
                        @endif
                    </div>
                    @if ($order->buyer_message)
                        <p class="mt-4 whitespace-pre-line rounded-xl bg-paper p-3 text-sm leading-6 text-secondary"><span class="font-semibold text-ink">Your note:</span> {{ $order->buyer_message }}</p>
                    @endif
                </article>
            @empty
                <x-empty-state
                    title="No order requests yet"
                    description="Add a product or service to your cart and send a request to its seller."
                    action="Explore listings"
                    :href="route('listings.index')"
                />
            @endforelse
        </div>

        <x-pagination :paginator="$orders" />
    </section>
</x-app-layout>
