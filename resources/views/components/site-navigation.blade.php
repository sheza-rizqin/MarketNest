<header class="sticky top-0 z-30 border-b border-sand bg-white">
    <nav x-data="{ open: false }" class="mx-auto max-w-7xl px-5 sm:px-8" aria-label="Main navigation">
        <div class="flex min-h-[76px] items-center justify-between gap-5">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="MarketNest home">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-navy text-lg font-bold tracking-tight text-white">M</span>
                <span>
                    <span class="block text-lg font-bold leading-5 tracking-tight text-navy">MarketNest</span>
                    <span class="mt-1 hidden text-[11px] leading-4 text-muted sm:block">Discover locally. Buy confidently. Sell easily.</span>
                </span>
            </a>

            <div class="hidden items-center gap-4 xl:flex">
                <a href="{{ route('listings.index') }}" class="text-sm font-medium text-secondary transition hover:text-navy">Explore</a>
                <a href="{{ route('categories.index') }}" class="text-sm font-medium text-secondary transition hover:text-navy">Categories</a>
                <a href="{{ route('home') }}#cities" class="text-sm font-medium text-secondary transition hover:text-navy">Cities</a>
                <a href="{{ route('cart.index') }}" class="text-sm font-medium text-secondary transition hover:text-navy">Cart ({{ count(session('cart_listing_ids', [])) }})</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-secondary transition hover:text-navy">Dashboard</a>
                    <a href="{{ route('favorites.index') }}" class="text-sm font-medium text-secondary transition hover:text-navy">Saved listings</a>
                    <a href="{{ route('dashboard.enquiries') }}" class="text-sm font-medium text-secondary transition hover:text-navy">Messages</a>
                    <a href="{{ route('dashboard.orders') }}" class="text-sm font-medium text-secondary transition hover:text-navy">My orders</a>
                    <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-secondary transition hover:text-navy">{{ Auth::user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-secondary transition hover:text-navy">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-secondary transition hover:text-navy">Sign in</a>
                    <x-button href="{{ route('register') }}" variant="primary" class="min-h-10 rounded-lg px-4">Start selling</x-button>
                @endauth
            </div>

            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-sand text-navy xl:hidden" @click="open = !open" :aria-expanded="open.toString()" aria-label="Toggle navigation">
                <svg x-show="!open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
                <svg x-show="open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <div x-show="open" x-cloak x-transition.opacity class="space-y-1 border-t border-sand py-4 xl:hidden">
            <a href="{{ route('listings.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">Explore listings</a>
            <a href="{{ route('categories.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">Categories</a>
            <a href="{{ route('home') }}#cities" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">Popular cities</a>
            <a href="{{ route('cart.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">Cart ({{ count(session('cart_listing_ids', [])) }})</a>
            @auth
                <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">Dashboard</a>
                <a href="{{ route('favorites.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">Saved listings</a>
                <a href="{{ route('dashboard.enquiries') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">Messages</a>
                <a href="{{ route('dashboard.orders') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">My orders</a>
                <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-paper hover:text-navy">{{ Auth::user()->name }} · Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="px-3 py-2">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-secondary hover:text-navy">Sign out</button>
                </form>
            @else
                <div class="flex items-center gap-4 px-3 py-2">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-secondary hover:text-navy">Sign in</a>
                    <a href="{{ route('register') }}" class="text-sm font-semibold text-orange">Create account</a>
                </div>
            @endauth
        </div>
    </nav>
</header>
