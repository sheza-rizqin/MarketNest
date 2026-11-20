<footer class="border-t border-sand bg-white">
    <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-navy text-sm font-bold text-white">M</span>
                    <span class="text-lg font-bold tracking-tight text-navy">MarketNest</span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-6 text-muted">Discover locally. Buy confidently. Sell easily. A more thoughtful way to find the good things around you.</p>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-ink">Explore</h2>
                <div class="mt-4 flex flex-col gap-3 text-sm text-muted">
                    <a href="{{ route('listings.index') }}" class="hover:text-navy">Browse listings</a>
                    <a href="{{ route('categories.index') }}" class="hover:text-navy">All categories</a>
                    <a href="{{ route('home') }}#cities" class="hover:text-navy">Popular cities</a>
                </div>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-ink">Your MarketNest</h2>
                <div class="mt-4 flex flex-col gap-3 text-sm text-muted">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-navy">Seller dashboard</a>
                        <a href="{{ route('profile.edit') }}" class="hover:text-navy">Your profile</a>
                    @else
                        <a href="{{ route('register') }}" class="hover:text-navy">Create an account</a>
                        <a href="{{ route('login') }}" class="hover:text-navy">Sign in</a>
                    @endauth
                </div>
            </div>
        </div>
        <div class="mt-10 flex flex-col gap-3 border-t border-sand pt-5 text-xs text-muted sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} MarketNest</p>
            <p>Discover locally. Buy confidently. Sell easily.</p>
        </div>
    </div>
</footer>
