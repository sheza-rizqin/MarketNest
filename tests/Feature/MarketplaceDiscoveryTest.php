<?php

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;

it('combines keyword, type, category, location, and price filters using shareable get parameters', function () {
    $root = Category::factory()->create(['name' => 'Products', 'slug' => 'products']);
    $category = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics', 'parent_id' => $root->id]);
    $subcategory = Category::factory()->create(['name' => 'Phones', 'slug' => 'phones', 'parent_id' => $category->id]);
    $otherCategory = Category::factory()->create(['name' => 'Furniture', 'slug' => 'furniture', 'parent_id' => $root->id]);

    $match = Listing::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'listing_type' => 'product',
        'title' => 'Compact handset',
        'description' => 'A refurbished handset with a clear screen.',
        'country' => 'India',
        'state' => 'Karnataka',
        'city' => 'Bengaluru',
        'area' => 'Koramangala',
        'price' => 15000,
    ]);
    Listing::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'description' => 'A refurbished handset in another area.',
        'city' => 'Bengaluru',
        'area' => 'Indiranagar',
        'price' => 15000,
        'title' => 'Different area handset',
    ]);
    Listing::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'description' => 'A refurbished handset from another country.',
        'country' => 'Canada',
        'state' => 'Karnataka',
        'city' => 'Bengaluru',
        'area' => 'Koramangala',
        'price' => 15000,
        'title' => 'Different country handset',
    ]);
    Listing::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'description' => 'A refurbished handset from another state.',
        'country' => 'India',
        'state' => 'Kerala',
        'city' => 'Bengaluru',
        'area' => 'Koramangala',
        'price' => 15000,
        'title' => 'Different state handset',
    ]);
    Listing::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'description' => 'A refurbished handset from another city.',
        'country' => 'India',
        'state' => 'Karnataka',
        'city' => 'Mysuru',
        'area' => 'Koramangala',
        'price' => 15000,
        'title' => 'Different city handset',
    ]);
    Listing::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'listing_type' => 'service',
        'description' => 'Refurbished handset repair service.',
        'price' => 15000,
        'title' => 'Handset repair service',
    ]);
    Listing::factory()->create([
        'category_id' => $otherCategory->id,
        'description' => 'A refurbished oak cabinet.',
        'price' => 15000,
        'title' => 'Oak cabinet',
    ]);
    Listing::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'description' => 'A refurbished handset at a higher price.',
        'price' => 25000,
        'title' => 'Premium handset',
    ]);
    Listing::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'description' => 'A refurbished handset no longer available.',
        'status' => 'archived',
        'price' => 15000,
        'title' => 'Archived handset',
    ]);

    $this->get(route('listings.index', [
        'q' => 'refurbished handset',
        'listing_type' => 'product',
        'category' => $category->slug,
        'subcategory' => $subcategory->slug,
        'country' => 'India',
        'state' => 'Karnataka',
        'city' => 'Bengaluru',
        'area' => 'Koramangala',
        'min_price' => 10000,
        'max_price' => 20000,
        'sort' => 'newest',
    ]))
        ->assertOk()
        ->assertSeeText($match->title)
        ->assertDontSeeText('Different area handset')
        ->assertDontSeeText('Different country handset')
        ->assertDontSeeText('Different state handset')
        ->assertDontSeeText('Different city handset')
        ->assertDontSeeText('Handset repair service')
        ->assertDontSeeText('Oak cabinet')
        ->assertDontSeeText('Premium handset')
        ->assertDontSeeText('Archived handset')
        ->assertSee('name="area"', false)
        ->assertSee('value="Koramangala"', false)
        ->assertSee('name="min_price"', false)
        ->assertSee('value="10000"', false)
        ->assertSee('Clear filters');
});

it('sorts active results by date and price and keeps contact-priced listings last', function () {
    $oldest = Listing::factory()->create([
        'title' => 'Oldest listing',
        'price' => 50,
        'created_at' => now()->subDays(3),
    ]);
    $middle = Listing::factory()->create([
        'title' => 'Middle listing',
        'price' => 10,
        'created_at' => now()->subDay(),
    ]);
    $newest = Listing::factory()->create([
        'title' => 'Newest listing',
        'price' => 25,
        'created_at' => now(),
    ]);
    Listing::factory()->create([
        'title' => 'Contact price listing',
        'price' => null,
        'price_type' => 'contact',
    ]);

    $this->get(route('listings.index', ['sort' => 'oldest']))
        ->assertOk()
        ->assertSeeInOrder([$oldest->title, $middle->title, $newest->title]);

    $this->get(route('listings.index', ['sort' => 'newest']))
        ->assertOk()
        ->assertSeeInOrder([$newest->title, $middle->title, $oldest->title]);

    $this->get(route('listings.index', ['sort' => 'price_asc']))
        ->assertOk()
        ->assertSeeInOrder([$middle->title, $newest->title, $oldest->title, 'Contact price listing']);

    $this->get(route('listings.index', ['sort' => 'price_desc']))
        ->assertOk()
        ->assertSeeInOrder([$oldest->title, $newest->title, $middle->title, 'Contact price listing']);
});

it('validates filter values and category-subcategory relationships', function () {
    $category = Category::factory()->create(['slug' => 'electronics']);
    $subcategory = Category::factory()->create(['slug' => 'phones', 'parent_id' => $category->id]);
    $otherCategory = Category::factory()->create(['slug' => 'furniture']);
    Category::factory()->create(['slug' => 'chairs', 'parent_id' => $otherCategory->id]);

    $this->get(route('listings.index', [
        'category' => $category->slug,
        'subcategory' => 'chairs',
    ]))->assertRedirect()->assertSessionHasErrors('subcategory');

    $this->get(route('listings.index', [
        'listing_type' => 'other',
        'min_price' => 200,
        'max_price' => 100,
    ]))->assertRedirect()->assertSessionHasErrors(['listing_type', 'max_price']);

    $this->get(route('listings.index', ['category' => 'missing-category']))
        ->assertRedirect()
        ->assertSessionHasErrors('category');

    expect($subcategory->parent_id)->toBe($category->id);
});

it('preserves filters through pagination and provides a clear-filters link', function () {
    foreach (range(1, 13) as $number) {
        Listing::factory()->create([
            'title' => 'Matched chair '.$number,
            'listing_type' => 'product',
        ]);
    }

    $this->get(route('listings.index', ['q' => 'Matched chair', 'listing_type' => 'product']))
        ->assertOk()
        ->assertSee('page=2')
        ->assertSee('q=Matched%20chair')
        ->assertSee('Clear filters');
});

it('shows only the signed-in user’s active saved listings and protects the saved page', function () {
    $buyer = User::factory()->create();
    $otherBuyer = User::factory()->create();
    $saved = Listing::factory()->create(['title' => 'My saved listing']);
    $inactive = Listing::factory()->create(['title' => 'Inactive saved listing', 'status' => 'archived']);
    $otherSaved = Listing::factory()->create(['title' => 'Someone else’s saved listing']);

    Favorite::factory()->create(['user_id' => $buyer->id, 'listing_id' => $saved->id]);
    Favorite::factory()->create(['user_id' => $buyer->id, 'listing_id' => $inactive->id]);
    Favorite::factory()->create(['user_id' => $otherBuyer->id, 'listing_id' => $otherSaved->id]);

    $this->get(route('favorites.index'))->assertRedirect(route('login'));

    $this->actingAs($buyer)
        ->get(route('favorites.index'))
        ->assertOk()
        ->assertSeeText($saved->title)
        ->assertDontSeeText($inactive->title)
        ->assertDontSeeText($otherSaved->title);
});
