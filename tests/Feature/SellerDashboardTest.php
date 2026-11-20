<?php

use App\Models\Enquiry;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('protects the seller dashboard overview and listings page from guests', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('dashboard.listings'))->assertRedirect(route('login'));
    $this->get(route('dashboard.enquiries'))->assertRedirect(route('login'));
});

it('shows sellers only the enquiries for their own listings and provides a reply link', function () {
    $seller = User::factory()->create();
    $otherSeller = User::factory()->create();
    $listing = Listing::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Seller camera listing',
    ]);
    $otherListing = Listing::factory()->create([
        'user_id' => $otherSeller->id,
        'title' => 'Other seller table listing',
    ]);
    Enquiry::factory()->create([
        'listing_id' => $listing->id,
        'sender_name' => 'Interested Buyer',
        'sender_email' => 'buyer@example.test',
        'message' => 'Can I see this camera this weekend?',
    ]);
    Enquiry::factory()->create([
        'listing_id' => $otherListing->id,
        'sender_name' => 'Another Buyer',
        'sender_email' => 'other-buyer@example.test',
        'message' => 'Is this table still available?',
    ]);

    $this->actingAs($seller)
        ->get(route('dashboard.enquiries'))
        ->assertOk()
        ->assertSeeText('Seller inbox')
        ->assertSeeText('Seller camera listing')
        ->assertSeeText('Interested Buyer')
        ->assertSeeText('buyer@example.test')
        ->assertSeeText('Can I see this camera this weekend?')
        ->assertSee('mailto:buyer@example.test', false)
        ->assertDontSeeText('Other seller table listing')
        ->assertDontSeeText('other-buyer@example.test');
});

it('shows database-backed statistics and recent listings only for the signed-in seller', function () {
    $seller = User::factory()->create(['name' => 'Morgan Seller']);
    $otherSeller = User::factory()->create();

    $active = Listing::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Seller active listing',
        'status' => 'active',
    ]);
    Listing::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Second seller active listing',
        'status' => 'active',
    ]);
    Listing::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Seller sold listing',
        'status' => 'sold',
    ]);
    Listing::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Seller archived listing',
        'status' => 'archived',
    ]);
    Listing::factory()->create([
        'user_id' => $otherSeller->id,
        'title' => 'Another seller private listing',
        'status' => 'active',
    ]);

    $this->actingAs($seller)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Welcome back, Morgan Seller')
        ->assertSeeText('Total listings')
        ->assertSeeText('Active')
        ->assertSeeText('Sold')
        ->assertSeeText('Archived')
        ->assertSeeText('4')
        ->assertSeeText('2')
        ->assertSeeText('1')
        ->assertSeeText($active->title)
        ->assertSeeText('Seller sold listing')
        ->assertDontSeeText('Another seller private listing')
        ->assertSee(route('dashboard.listings'), false)
        ->assertSee(route('favorites.index'), false)
        ->assertSee(route('dashboard.enquiries'), false)
        ->assertSee(route('profile.edit'), false)
        ->assertSee(route('listings.create'), false);
});

it('shows zeroed statistics and useful empty states to a new seller', function () {
    $seller = User::factory()->create();

    $this->actingAs($seller)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Welcome back, '.$seller->name)
        ->assertSeeText('0')
        ->assertSeeText('Your first listing starts here')
        ->assertSee(route('listings.create'), false);

    $this->get(route('dashboard.listings'))
        ->assertOk()
        ->assertSeeText('No listings yet')
        ->assertSee(route('listings.create'), false);
});

it('lists only the seller’s own listings with listing details and management actions', function () {
    Storage::fake('public');

    $seller = User::factory()->create();
    $otherSeller = User::factory()->create();
    foreach (range(1, 10) as $number) {
        Listing::factory()->create([
            'user_id' => $seller->id,
            'title' => 'Seller listing '.$number,
        ]);
    }

    $listing = Listing::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Oak desk for sale',
        'price' => 7500,
        'city' => 'Mysuru',
        'status' => 'active',
    ]);
    $image = ListingImage::factory()->create([
        'listing_id' => $listing->id,
        'image_path' => 'listings/oak-desk.jpg',
        'is_primary' => true,
    ]);
    Storage::disk('public')->put($image->image_path, 'image fixture');

    Listing::factory()->create([
        'user_id' => $otherSeller->id,
        'title' => 'Different seller listing',
    ]);

    $this->actingAs($seller)
        ->get(route('dashboard.listings'))
        ->assertOk()
        ->assertSeeText('My listings')
        ->assertSeeText('Oak desk for sale')
        ->assertSeeText('Mysuru')
        ->assertSeeText('₹7,500')
        ->assertSee(route('listings.images.show', [$listing, $image]), false)
        ->assertSee(route('listings.show', $listing), false)
        ->assertSee(route('listings.edit', $listing), false)
        ->assertSee(route('listings.status.update', $listing), false)
        ->assertSee(route('listings.destroy', $listing), false)
        ->assertSee('Delete this listing and its photos?', false)
        ->assertDontSeeText('Different seller listing')
        ->assertSee('page=2');

    $this->get(route('dashboard.listings', ['page' => 2]))
        ->assertOk()
        ->assertSeeText('Seller listing 1')
        ->assertDontSeeText('Different seller listing');
});

it('lets the owner update status and delete a listing with clear notifications', function () {
    $seller = User::factory()->create();
    $listing = Listing::factory()->create([
        'user_id' => $seller->id,
        'status' => 'active',
    ]);

    $this->actingAs($seller)
        ->patch(route('listings.status.update', $listing), ['status' => 'sold'])
        ->assertRedirect(route('dashboard.listings'))
        ->assertSessionHas('status', 'Listing status updated.');

    expect($listing->fresh()->status)->toBe('sold');

    $this->delete(route('listings.destroy', $listing))
        ->assertRedirect(route('dashboard.listings'))
        ->assertSessionHas('status', 'Listing deleted.');

    expect(Listing::query()->whereKey($listing->id)->exists())->toBeFalse();
});
