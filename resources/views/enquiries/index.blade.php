<x-app-layout title="Messages | MarketNest">
    <section class="mx-auto max-w-5xl px-5 py-9 sm:px-8 sm:py-12">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Seller dashboard', 'url' => route('dashboard')],
            ['label' => 'Messages'],
        ]" />

        <div class="mt-6">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Seller inbox</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-navy sm:text-4xl">Messages about your listings</h1>
            <p class="mt-2 text-sm text-muted">Enquiries are saved here so you can read and reply to them.</p>
        </div>

        <div class="mt-7 space-y-4">
            @forelse ($enquiries as $enquiry)
                <article class="rounded-2xl border border-sand bg-white p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">About</p>
                            <a href="{{ $enquiry->listing->status === 'active' ? route('listings.show', $enquiry->listing) : route('listings.edit', $enquiry->listing) }}" class="mt-1 inline-block font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">
                                {{ $enquiry->listing->title }}
                            </a>
                        </div>
                        <time datetime="{{ $enquiry->created_at->toIso8601String() }}" class="text-sm text-muted">
                            {{ $enquiry->created_at->toFormattedDateString() }}
                        </time>
                    </div>

                    <div class="mt-4 border-t border-sand pt-4">
                        <p class="font-semibold text-ink">{{ $enquiry->sender_name }}</p>
                        <a href="mailto:{{ $enquiry->sender_email }}" class="mt-1 inline-block text-sm text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">
                            {{ $enquiry->sender_email }}
                        </a>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-secondary">{{ $enquiry->message }}</p>
                        <a href="mailto:{{ $enquiry->sender_email }}" class="mt-4 inline-flex text-sm font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">
                            Reply by email
                        </a>
                    </div>
                </article>
            @empty
                <x-empty-state
                    title="No messages yet"
                    description="When someone contacts you about a listing, their message will appear here."
                    action="Manage my listings"
                    :href="route('dashboard.listings')"
                />
            @endforelse
        </div>

        <x-pagination :paginator="$enquiries" />
    </section>
</x-app-layout>
