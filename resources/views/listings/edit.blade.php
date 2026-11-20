<x-app-layout :title="'Manage '.$listing->title.' | MarketNest'">
    <section class="mx-auto max-w-3xl px-5 py-10 sm:px-8">
        <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'My listings', 'url' => route('dashboard.listings')], ['label' => $listing->title]]" />
        <h1 class="text-3xl font-semibold tracking-tight text-navy">Manage listing</h1>
        @if ($errors->has('status'))
            <p class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('status') }}</p>
        @endif

        <form method="POST" action="{{ route('listings.update', $listing) }}" enctype="multipart/form-data" class="mt-8 rounded-2xl border border-sand bg-white p-5 sm:p-8">
            @csrf
            @method('PUT')
            @include('listings._form', ['listing' => $listing])
            <x-button type="submit" variant="accent" class="mt-6">Save changes</x-button>
        </form>

        <div class="mt-10 border-t border-sand pt-8">
            <h2 class="text-xl font-semibold text-navy">Photos</h2>
            @error('images') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                @forelse ($listing->images as $index => $image)
                    <div>
                        <img src="{{ route('listings.images.show', [$listing, $image]) }}" alt="{{ $listing->title }}" class="h-36 w-full rounded-xl object-cover">
                        @if ($image->is_primary)
                            <span class="mt-2 inline-flex rounded-full bg-paper px-2.5 py-1 text-xs font-semibold text-navy">Primary photo</span>
                        @else
                            <form method="POST" action="{{ route('listings.images.primary', [$listing, $image]) }}" class="mt-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm font-semibold text-navy underline decoration-sand underline-offset-4">Make primary</button>
                            </form>
                        @endif
                        <div class="mt-2 flex gap-3">
                            <form method="POST" action="{{ route('listings.images.reorder', [$listing, $image]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" @disabled($index === 0) class="text-sm font-semibold text-muted underline decoration-sand underline-offset-4 disabled:opacity-40">Move up</button>
                            </form>
                            <form method="POST" action="{{ route('listings.images.reorder', [$listing, $image]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" @disabled($index === $listing->images->count() - 1) class="text-sm font-semibold text-muted underline decoration-sand underline-offset-4 disabled:opacity-40">Move down</button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('listings.images.destroy', [$listing, $image]) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-red-700">Remove photo</button>
                        </form>
                    </div>
                @empty
                    <p class="col-span-full text-sm text-muted">No photos have been added to this listing.</p>
                @endforelse
            </div>
        </div>

        <form method="POST" action="{{ route('listings.destroy', $listing) }}" class="mt-10 border-t border-sand pt-6" onsubmit="return confirm('Delete this listing and its photos?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-semibold text-red-700">Delete listing</button>
        </form>
    </section>
</x-app-layout>
