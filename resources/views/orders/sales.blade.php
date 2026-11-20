<x-app-layout title="Sales requests | MarketNest">
    <section class="mx-auto max-w-5xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Seller dashboard', 'url' => route('dashboard')],
            ['label' => 'Sales requests'],
        ]" />

        <div class="mt-6">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Seller activity</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">Sales requests</h1>
            <p class="mt-2 text-sm text-muted">Review requests from buyers and update their status. No payment is processed by MarketNest.</p>
        </div>

        <div class="mt-7 space-y-4">
            @forelse ($orders as $order)
                <article class="rounded-2xl border border-sand bg-white p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Request #{{ $order->id }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-navy">{{ $order->buyer->name }}</h2>
                            <a href="mailto:{{ $order->buyer->email }}" class="mt-1 inline-block text-sm text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">{{ $order->buyer->email }}</a>
                            <p class="mt-1 text-sm text-muted">Received {{ $order->created_at->toFormattedDateString() }}</p>
                        </div>
                        <span class="rounded-full bg-paper px-3 py-1 text-sm font-semibold capitalize text-navy">{{ $order->status }}</span>
                    </div>

                    <ul class="mt-4 divide-y divide-sand border-y border-sand">
                        @foreach ($order->items as $item)
                            <li class="flex flex-wrap justify-between gap-2 py-3 text-sm">
                                <span class="font-medium text-ink">{{ $item->listing_title }} <span class="font-normal text-muted">· {{ ucfirst($item->listing_type) }}</span></span>
                                <span class="text-secondary">
                                    @if ($item->price_type === 'contact' || $item->listed_price === null)
                                        Price to confirm
                                    @else
                                        ₹{{ number_format((float) $item->listed_price, 2) }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($order->buyer_message)
                        <p class="mt-4 whitespace-pre-line rounded-xl bg-paper p-3 text-sm leading-6 text-secondary"><span class="font-semibold text-ink">Buyer’s note:</span> {{ $order->buyer_message }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-navy">
                            @if ($order->amount_due === null)
                                Confirm price with the buyer
                            @else
                                Listed total: ₹{{ number_format((float) $order->amount_due, 2) }}
                            @endif
                        </p>
                        @if ($order->status === 'pending')
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('dashboard.sales.status', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="accepted">
                                    <x-button type="submit">Accept request</x-button>
                                </form>
                                <form method="POST" action="{{ route('dashboard.sales.status', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="declined">
                                    <x-button type="submit" variant="outline">Decline</x-button>
                                </form>
                            </div>
                        @elseif ($order->status === 'accepted')
                            <form method="POST" action="{{ route('dashboard.sales.status', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <x-button type="submit">Mark completed</x-button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <x-empty-state
                    title="No sales requests yet"
                    description="When a buyer requests one of your active listings, it will appear here."
                    action="Manage my listings"
                    :href="route('dashboard.listings')"
                />
            @endforelse
        </div>

        <x-pagination :paginator="$orders" />
    </section>
</x-app-layout>
