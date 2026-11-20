<?php

it('renders the marketplace homepage with guest navigation', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Discover locally. Buy confidently. Sell easily.')
        ->assertSee('Create account')
        ->assertSee('Sign in');
});

it('allows guests to open the login and registration pages', function () {
    $this->get(route('login'))->assertOk();
    $this->get(route('register'))->assertOk();
});
