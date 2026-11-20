<?php

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes the marketplace model relationships without serializing seller contact details', function () {
    $seller = User::factory()->create([
        'email' => 'seller@example.test',
        'phone' => '+91 90000 10001',
    ]);
    $buyer = User::factory()->create();
    $parent = Category::factory()->create();
    $category = Category::factory()->create(['parent_id' => $parent->id]);
    $listing = Listing::factory()->create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
    ]);
    $image = ListingImage::factory()->create(['listing_id' => $listing->id]);
    $favorite = Favorite::factory()->create([
        'user_id' => $buyer->id,
        'listing_id' => $listing->id,
    ]);
    $enquiry = Enquiry::factory()->create(['listing_id' => $listing->id]);

    expect($seller->listings->contains($listing))->toBeTrue()
        ->and($buyer->favoriteListings->contains($listing))->toBeTrue()
        ->and($parent->children->contains($category))->toBeTrue()
        ->and($category->parent->is($parent))->toBeTrue()
        ->and($category->listings->contains($listing))->toBeTrue()
        ->and($listing->user->is($seller))->toBeTrue()
        ->and($listing->category->is($category))->toBeTrue()
        ->and($listing->images->contains($image))->toBeTrue()
        ->and($listing->favoritedBy->contains($buyer))->toBeTrue()
        ->and($listing->enquiries->contains($enquiry))->toBeTrue()
        ->and($image->listing->is($listing))->toBeTrue()
        ->and($favorite->user->is($buyer))->toBeTrue()
        ->and($favorite->listing->is($listing))->toBeTrue()
        ->and($enquiry->listing->is($listing))->toBeTrue();

    $serializedSeller = $seller->toArray();

    expect($serializedSeller)->not->toHaveKey('email')
        ->not->toHaveKey('phone');
});

it('prevents duplicate favorites and cascades listing-owned records on seller deletion', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $seller->id]);

    expect(fn () => Listing::factory()->create(['slug' => $listing->slug]))
        ->toThrow(QueryException::class);

    Favorite::factory()->create([
        'user_id' => $buyer->id,
        'listing_id' => $listing->id,
    ]);

    expect(fn () => Favorite::factory()->create([
        'user_id' => $buyer->id,
        'listing_id' => $listing->id,
    ]))->toThrow(QueryException::class);

    $image = ListingImage::factory()->create(['listing_id' => $listing->id]);
    $enquiry = Enquiry::factory()->create(['listing_id' => $listing->id]);

    $seller->delete();

    expect(Listing::query()->whereKey($listing->id)->exists())->toBeFalse()
        ->and(ListingImage::query()->whereKey($image->id)->exists())->toBeFalse()
        ->and(Favorite::query()->where('listing_id', $listing->id)->exists())->toBeFalse()
        ->and(Enquiry::query()->whereKey($enquiry->id)->exists())->toBeFalse();
});

it('restricts deleting categories that still have children or listings', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);
    $listing = Listing::factory()->create(['category_id' => $child->id]);

    expect(fn () => $parent->delete())->toThrow(QueryException::class)
        ->and(fn () => $child->delete())->toThrow(QueryException::class);
});
