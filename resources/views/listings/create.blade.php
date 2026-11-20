<x-app-layout title="Create listing | MarketNest">
    <section class="mx-auto max-w-3xl px-5 py-10 sm:px-8">
        <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'My listings', 'url' => route('dashboard.listings')], ['label' => 'Create listing']]" />
        <h1 class="text-3xl font-semibold tracking-tight text-navy">Create a listing</h1>
        <p class="mt-2 text-muted">Share a product or service with your community.</p>
        <form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data" class="mt-8 rounded-2xl border border-sand bg-white p-5 sm:p-8">
            @csrf
            @include('listings._form', ['listing' => null])
            <x-button type="submit" variant="accent" class="mt-6">Create listing</x-button>
        </form>
    </section>
</x-app-layout>
