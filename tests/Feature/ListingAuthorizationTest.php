<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('protects the dashboard, profile, and seller listing routes from guests', function () {
    $listing = Listing::factory()->create();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->get(route('listings.create'))->assertRedirect(route('login'));
    $this->get(route('listings.edit', $listing))->assertRedirect(route('login'));
    $this->patch(route('listings.status.update', $listing), ['status' => 'sold'])
        ->assertRedirect(route('login'));
});

it('shows sellers only their own listings in the dashboard', function () {
    $seller = User::factory()->create();
    $otherSeller = User::factory()->create();
    $ownListing = Listing::factory()->create(['user_id' => $seller->id, 'title' => 'My seller listing']);
    Listing::factory()->create(['user_id' => $otherSeller->id, 'title' => 'Another seller listing']);

    $this->actingAs($seller)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText($ownListing->title)
        ->assertDontSeeText('Another seller listing');
});

it('shows only active listings publicly and keeps seller contact details private', function () {
    $seller = User::factory()->create([
        'name' => 'Private Seller Name',
        'email' => 'private-seller@example.test',
        'phone' => '+91 98765 43210',
    ]);
    $active = Listing::factory()->create(['user_id' => $seller->id, 'title' => 'Public active item']);
    $inactive = Listing::factory()->create(['status' => 'archived', 'title' => 'Hidden archived item']);

    $this->get(route('listings.index'))
        ->assertOk()
        ->assertSeeText($active->title)
        ->assertDontSeeText($inactive->title);

    $this->get(route('listings.show', $active))
        ->assertOk()
        ->assertSeeText($active->title)
        ->assertSeeText($seller->name)
        ->assertDontSeeText($seller->email)
        ->assertDontSeeText($seller->phone);

    $this->get(route('listings.show', $inactive))->assertNotFound();
});

it('prevents another seller from updating, deleting, or changing the status of a listing', function () {
    $owner = User::factory()->create();
    $otherSeller = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $owner->id, 'status' => 'active']);
    $newCategory = Category::factory()->create();

    $this->actingAs($otherSeller)
        ->put(route('listings.update', $listing), [
            'category_id' => $newCategory->id,
            'listing_type' => 'service',
            'title' => 'Hijacked title',
            'description' => 'Changed by another seller',
            'price' => 1,
            'price_type' => 'fixed',
            'country' => 'India',
            'state' => 'Karnataka',
            'city' => 'Bengaluru',
        ])
        ->assertForbidden();

    $this->actingAs($otherSeller)
        ->patch(route('listings.status.update', $listing), ['status' => 'sold'])
        ->assertForbidden();

    $this->actingAs($otherSeller)
        ->delete(route('listings.destroy', $listing))
        ->assertForbidden();

    expect($listing->fresh()->title)->not->toBe('Hijacked title')
        ->and($listing->fresh()->status)->toBe('active')
        ->and(Listing::query()->whereKey($listing->id)->exists())->toBeTrue();
});

it('ignores client-supplied ownership and status fields when the owner updates a listing', function () {
    $owner = User::factory()->create();
    $otherSeller = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $owner->id, 'status' => 'active']);

    $this->actingAs($owner)
        ->put(route('listings.update', $listing), [
            'category_id' => $listing->category_id,
            'listing_type' => 'product',
            'title' => 'Owner updated title',
            'description' => 'Updated by the listing owner',
            'price' => 25,
            'price_type' => 'fixed',
            'country' => 'India',
            'state' => 'Karnataka',
            'city' => 'Mysuru',
            'user_id' => $otherSeller->id,
            'status' => 'archived',
        ])
        ->assertRedirect(route('listings.edit', $listing->fresh()));

    expect($listing->fresh()->title)->toBe('Owner updated title')
        ->and($listing->fresh()->user_id)->toBe($owner->id)
        ->and($listing->fresh()->status)->toBe('active');

    $listing->refresh();
    $this->actingAs($owner)
        ->patch(route('listings.status.update', $listing), ['status' => 'sold'])
        ->assertRedirect(route('dashboard.listings'));

    expect($listing->fresh()->status)->toBe('sold');
});

it('scopes image deletion to the authorized listing and permits its owner to upload images', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $otherSeller = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $owner->id]);
    $otherListing = Listing::factory()->create(['user_id' => $otherSeller->id]);
    $otherImage = ListingImage::factory()->create(['listing_id' => $otherListing->id]);

    $this->actingAs($owner)
        ->delete(route('listings.images.destroy', [$listing, $otherImage]))
        ->assertNotFound();

    expect($otherImage->fresh())->not->toBeNull();

    $this->actingAs($otherSeller)
        ->post(route('listings.images.store', $listing), ['images' => [fakePngUpload()]])
        ->assertForbidden();

    $this->actingAs($owner)
        ->post(route('listings.images.store', $listing), ['images' => [fakePngUpload()]])
        ->assertRedirect(route('listings.edit', $listing));

    $image = $listing->images()->firstOrFail();
    Storage::disk('public')->assertExists($image->image_path);
    expect($image->is_primary)->toBeTrue();

    $imageResponse = $this->get(route('listings.images.show', [$listing, $image]))->assertOk();
    expect($imageResponse->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');

    $listing->update(['status' => 'archived']);
    auth()->logout();

    $this->get(route('listings.images.show', [$listing, $image]))->assertNotFound();

    $this->actingAs($owner)
        ->delete(route('listings.images.destroy', [$listing, $image]))
        ->assertRedirect(route('listings.edit', $listing));

    Storage::disk('public')->assertMissing($image->image_path);
    expect($image->fresh())->toBeNull();
});

function fakePngUpload(): UploadedFile
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/r9sAAAAASUVORK5CYII=');

    return UploadedFile::fake()->createWithContent('photo.png', $png);
}

it('redirects authenticated users to the intended destination after login', function () {
    $user = User::factory()->create();
    $this->get(route('profile.edit'));

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('profile.edit'));
});
