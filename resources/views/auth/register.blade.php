<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-[.14em] text-orange">Make yourself at home</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-navy">Create your account</h1>
        <p class="mt-2 text-sm leading-6 text-muted">Discover locally. Buy confidently. Sell easily.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <x-form-input id="name" name="name" label="Your name" :value="old('name')" required autofocus autocomplete="name" />
        <x-form-input id="email" name="email" label="Email address" type="email" :value="old('email')" required autocomplete="username" />
        <x-form-input id="password" name="password" label="Password" type="password" required autocomplete="new-password" />
        <x-form-input id="password_confirmation" name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />

        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <a class="text-sm font-medium text-muted underline decoration-sand underline-offset-4 hover:text-navy" href="{{ route('login') }}">Already have an account?</a>
            <x-button type="submit" variant="accent">Create account</x-button>
        </div>
    </form>
</x-guest-layout>
