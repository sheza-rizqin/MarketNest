<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Welcome back</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-navy">Sign in to MarketNest</h1>
        <p class="mt-2 text-sm leading-6 text-muted">Pick up where you left off in your local marketplace.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <x-form-input id="email" name="email" label="Email address" type="email" :value="old('email')" required autofocus autocomplete="username" />
        <x-form-input id="password" name="password" label="Password" type="password" required autocomplete="current-password" />

        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-muted">
            <input id="remember_me" type="checkbox" class="rounded border-sand text-navy shadow-sm focus:ring-navy" name="remember">
            <span>Remember me</span>
        </label>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <a class="text-sm font-medium text-muted underline decoration-sand underline-offset-4 hover:text-navy" href="{{ route('password.request') }}">Forgot your password?</a>
            <x-button type="submit">Sign in</x-button>
        </div>
    </form>

    <p class="mt-7 border-t border-sand pt-5 text-center text-sm text-muted">
        New to MarketNest?
        <a href="{{ route('register') }}" class="font-semibold text-navy underline decoration-sand underline-offset-4 hover:decoration-orange">Create account</a>
    </p>
</x-guest-layout>
