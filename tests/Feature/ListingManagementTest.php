<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('completes the listing lifecycle with subcategories and multiple managed images', function () {
    Storage::fake('public');

    $seller = User::factory()->create();
    $products = Category::factory()->create(['name' => 'Products', 'slug' => 'products']);
    $category = Category::factory()->create([
        'name' => 'Home',
        'parent_id' => $products->id,
    ]);
    $subcategory = Category::factory()->create([
        'name' => 'Furniture',
        'parent_id' => $category->id,
    ]);

    $this->actingAs($seller)
        ->post(route('listings.store'), listingPayload($category, $subcategory, [
            listingImageUpload('desk-front.png'),
            listingImageUpload('desk-side.png'),
        ]))
        ->assertRedirect();

    $listing = $seller->listings()->firstOrFail();
    $images = $listing->images()->orderBy('sort_order')->get();

    expect($listing->category_id)->toBe($category->id)
        ->and($listing->subcategory_id)->toBe($subcategory->id)
        ->and($listing->slug)->toMatch('/^restored-oak-desk-\d+$/')
        ->and($images)->toHaveCount(2)
        ->and($images->where('is_primary', true))->toHaveCount(1)
        ->and(Storage::disk('public')->exists($images[0]->image_path))->toBeTrue()
        ->and(Storage::disk('public')->exists($images[1]->image_path))->toBeTrue();

    $this->get(route('listings.show', $listing))
        ->assertOk()
        ->assertSeeText('Restored oak desk')
        ->assertSeeText('Home / Furniture');

    $this->get(route('listings.edit', $listing))->assertOk();

    $this->put(route('listings.update', $listing), listingPayload($category, $subcategory, [
        listingImageUpload('desk-detail.png'),
    ], [
        'title' => 'Updated oak desk',
        'price_type' => 'contact',
        'price' => '120.00',
    ]))->assertRedirect(route('listings.edit', $listing->fresh()));

    $listing->refresh();
    $images = $listing->images()->orderBy('sort_order')->get();
    expect($listing->title)->toBe('Updated oak desk')
        ->and($listing->slug)->toBe('updated-oak-desk-'.$listing->id)
        ->and($listing->price)->toBeNull()
        ->and($images)->toHaveCount(3);

    $primaryImage = $images->last();
    $this->patch(route('listings.images.primary', [$listing, $primaryImage]))
        ->assertRedirect(route('listings.edit', $listing));
    expect($primaryImage->fresh()->is_primary)->toBeTrue()
        ->and($listing->images()->where('is_primary', true)->count())->toBe(1);

    $this->patch(route('listings.images.reorder', [$listing, $primaryImage]), ['direction' => 'up'])
        ->assertRedirect(route('listings.edit', $listing));
    expect($listing->images()->orderBy('sort_order')->pluck('id')->all())
        ->toBe([$images[0]->id, $images[2]->id, $images[1]->id]);

    $primaryPath = $primaryImage->image_path;
    $this->delete(route('listings.images.destroy', [$listing, $primaryImage]))
        ->assertRedirect(route('listings.edit', $listing));
    Storage::disk('public')->assertMissing($primaryPath);
    expect($listing->images()->where('is_primary', true)->count())->toBe(1);

    $this->patch(route('listings.status.update', $listing), ['status' => 'archived'])
        ->assertRedirect(route('dashboard.listings'));
    auth()->logout();
    $this->get(route('listings.show', $listing))->assertNotFound();
    $this->actingAs($seller)->get(route('listings.show', $listing))->assertOk();

    $remainingPaths = $listing->images()->pluck('image_path')->all();
    $this->delete(route('listings.destroy', $listing))->assertRedirect(route('dashboard.listings'));

    expect(Listing::query()->whereKey($listing->id)->exists())->toBeFalse();
    foreach ($remainingPaths as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

it('rejects a subcategory from a different parent and invalid price combinations', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();
    $otherSubcategory = Category::factory()->create(['parent_id' => $otherCategory->id]);

    $this->actingAs($seller)
        ->from(route('listings.create'))
        ->post(route('listings.store'), listingPayload($category, $otherSubcategory))
        ->assertSessionHasErrors('subcategory_id');

    $payload = listingPayload($category);
    unset($payload['price']);

    $this->from(route('listings.create'))
        ->post(route('listings.store'), $payload)
        ->assertSessionHasErrors('price');

    expect($seller->listings()->count())->toBe(0);
});

it('rejects category groups, nested subcategories used as categories, and mismatched listing types', function () {
    $seller = User::factory()->create();
    $products = Category::factory()->create(['slug' => 'products']);
    $services = Category::factory()->create(['slug' => 'services']);
    $electronics = Category::factory()->create(['slug' => 'electronics', 'parent_id' => $products->id]);
    $phones = Category::factory()->create(['slug' => 'phones', 'parent_id' => $electronics->id]);
    $cleaning = Category::factory()->create(['slug' => 'cleaning', 'parent_id' => $services->id]);

    $this->actingAs($seller)
        ->from(route('listings.create'))
        ->post(route('listings.store'), listingPayload($products, $electronics))
        ->assertSessionHasErrors('category_id');

    $this->from(route('listings.create'))
        ->post(route('listings.store'), listingPayload($phones))
        ->assertSessionHasErrors('category_id');

    $this->from(route('listings.create'))
        ->post(route('listings.store'), listingPayload($cleaning, null, [], ['listing_type' => 'product']))
        ->assertSessionHasErrors('category_id');

    expect($seller->listings()->count())->toBe(0);
});

function listingPayload(Category $category, ?Category $subcategory = null, array $images = [], array $overrides = []): array
{
    return array_merge([
        'listing_type' => 'product',
        'title' => 'Restored oak desk',
        'description' => 'Solid oak desk in good condition.',
        'category_id' => $category->id,
        'subcategory_id' => $subcategory?->id,
        'country' => 'India',
        'state' => 'Karnataka',
        'city' => 'Mysuru',
        'area' => 'Vijayanagar',
        'price' => '7500.00',
        'price_type' => 'fixed',
        'images' => $images,
    ], $overrides);
}

function listingImageUpload(string $name): UploadedFile
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/r9sAAAAASUVORK5CYII=');

    return UploadedFile::fake()->createWithContent($name, $png);
}
