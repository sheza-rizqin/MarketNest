<?php

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;

it('renders homepage categories, cities, and recent listings from active database records', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Home goods']);
    $listing = Listing::factory()->create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
        'title' => 'Locally listed side table',
        'city' => 'Mysuru',
    ]);
    Listing::factory()->create([
        'status' => 'archived',
        'title' => 'Archived item stays off the homepage',
        'city' => 'Archived City',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Discover locally. Buy confidently. Sell easily.')
        ->assertSeeText($category->name)
        ->assertSeeText($listing->title)
        ->assertSeeText('Mysuru')
        ->assertDontSeeText('Archived item stays off the homepage')
        ->assertDontSeeText('Archived City');
});

it('filters active listings by keyword, category, and city using shareable query parameters', function () {
    $category = Category::factory()->create(['name' => 'Furniture']);
    $match = Listing::factory()->create([
        'category_id' => $category->id,
        'title' => 'Oak reading chair',
        'city' => 'Kochi',
    ]);
    Listing::factory()->create([
        'title' => 'Oak reading chair elsewhere',
        'city' => 'Mysuru',
    ]);
    Listing::factory()->create([
        'title' => 'Archived Oak reading chair',
        'category_id' => $category->id,
        'city' => 'Kochi',
        'status' => 'archived',
    ]);

    $this->get(route('listings.index', [
        'q' => 'Oak reading chair',
        'category' => $category->slug,
        'city' => 'Kochi',
    ]))
        ->assertOk()
        ->assertSeeText($match->title)
        ->assertDontSeeText('Oak reading chair elsewhere')
        ->assertDontSeeText('Archived Oak reading chair');
});

it('lets signed-in users save and unsave an active listing', function () {
    $buyer = User::factory()->create();
    $listing = Listing::factory()->create();

    $this->actingAs($buyer)
        ->post(route('listings.favorite', $listing))
        ->assertRedirect();
    expect(Favorite::query()->where('user_id', $buyer->id)->where('listing_id', $listing->id)->exists())->toBeTrue();

    $this->post(route('listings.favorite', $listing))->assertRedirect();
    expect(Favorite::query()->where('user_id', $buyer->id)->where('listing_id', $listing->id)->exists())->toBeFalse();
});

it('prevents guests from saving listings and does not allow saving inactive listings', function () {
    $activeListing = Listing::factory()->create();
    $inactiveListing = Listing::factory()->create(['status' => 'sold']);

    $this->post(route('listings.favorite', $activeListing))->assertRedirect(route('login'));

    $buyer = User::factory()->create();
    $this->actingAs($buyer)
        ->post(route('listings.favorite', $inactiveListing))
        ->assertNotFound();

    expect(Favorite::query()->exists())->toBeFalse();
});
