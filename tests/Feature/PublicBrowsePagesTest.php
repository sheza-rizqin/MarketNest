<?php

use App\Models\Category;
use App\Models\Listing;

it('shows the database category tree and active listing counts', function () {
    $products = Category::factory()->create(['name' => 'Products', 'slug' => 'products']);
    $electronics = Category::factory()->create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'parent_id' => $products->id,
    ]);
    $phones = Category::factory()->create([
        'name' => 'Mobile phones',
        'slug' => 'mobile-phones',
        'parent_id' => $electronics->id,
    ]);
    Listing::factory()->create([
        'category_id' => $electronics->id,
        'subcategory_id' => $phones->id,
        'status' => 'active',
    ]);
    Listing::factory()->create([
        'category_id' => $electronics->id,
        'subcategory_id' => $phones->id,
        'status' => 'archived',
    ]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSeeText('Products')
        ->assertSeeText('Electronics')
        ->assertSeeText('Mobile phones')
        ->assertSeeText('1 active listing');
});

it('filters category, subcategory, city, and combined browse routes to matching active listings', function () {
    $products = Category::factory()->create(['slug' => 'products']);
    $electronics = Category::factory()->create(['slug' => 'electronics', 'parent_id' => $products->id]);
    $phones = Category::factory()->create(['slug' => 'mobile-phones', 'parent_id' => $electronics->id]);
    $otherCategory = Category::factory()->create(['slug' => 'furniture']);

    $bengaluruPhone = Listing::factory()->create([
        'category_id' => $electronics->id,
        'subcategory_id' => $phones->id,
        'city' => 'Bengaluru',
        'title' => 'Bengaluru phone listing',
    ]);
    Listing::factory()->create([
        'category_id' => $electronics->id,
        'subcategory_id' => $phones->id,
        'city' => 'Kochi',
        'title' => 'Kochi phone listing',
    ]);
    Listing::factory()->create([
        'category_id' => $otherCategory->id,
        'city' => 'Bengaluru',
        'title' => 'Bengaluru furniture listing',
    ]);
    Listing::factory()->create([
        'category_id' => $electronics->id,
        'subcategory_id' => $phones->id,
        'city' => 'Bengaluru',
        'title' => 'Archived phone listing',
        'status' => 'archived',
    ]);

    $this->get(route('categories.show', $electronics))
        ->assertOk()
        ->assertSeeText($bengaluruPhone->title)
        ->assertSeeText('Kochi phone listing')
        ->assertDontSeeText('Archived phone listing');

    $this->get(route('categories.show', [$electronics, $phones]))
        ->assertOk()
        ->assertSeeText('Bengaluru phone listing')
        ->assertSeeText('Kochi phone listing')
        ->assertSee(route('categories.show', [$electronics, $phones]));

    $this->get(route('cities.show', 'bengaluru'))
        ->assertOk()
        ->assertSeeText($bengaluruPhone->title)
        ->assertSeeText('Bengaluru furniture listing')
        ->assertDontSeeText('Kochi phone listing')
        ->assertDontSeeText('Archived phone listing');

    $this->get(route('cities.categories.show', ['bengaluru', $electronics]))
        ->assertOk()
        ->assertSeeText($bengaluruPhone->title)
        ->assertDontSeeText('Kochi phone listing')
        ->assertDontSeeText('Bengaluru furniture listing');

    $this->get(route('cities.categories.show', ['bengaluru', $electronics, $phones]))
        ->assertOk()
        ->assertSeeText($bengaluruPhone->title)
        ->assertDontSeeText('Kochi phone listing')
        ->assertSee(route('cities.categories.show', ['bengaluru', $electronics, $phones]));
});

it('returns not found for missing city, category, or mismatched category hierarchy', function () {
    $parent = Category::factory()->create(['slug' => 'electronics']);
    Category::factory()->create(['slug' => 'phones', 'parent_id' => $parent->id]);
    Category::factory()->create(['slug' => 'furniture']);
    Listing::factory()->create(['city' => 'Mysuru']);

    $this->get('/category/not-a-category')->assertNotFound();
    $this->get('/category/electronics/furniture')->assertNotFound();
    $this->get('/city/not-a-city')->assertNotFound();
    $this->get('/city/mysuru/electronics/furniture')->assertNotFound();
});
