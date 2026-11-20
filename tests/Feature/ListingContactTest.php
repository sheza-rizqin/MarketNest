<?php

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\SellerEnquiryReceived;
use Illuminate\Support\Facades\Notification;

it('shows listing details and real related listings without exposing seller contact details', function () {
    $seller = User::factory()->create([
        'name' => 'Local Seller',
        'email' => 'seller-private@example.test',
        'phone' => '+91 98888 12345',
    ]);
    $category = Category::factory()->create(['name' => 'Home']);
    $subcategory = Category::factory()->create(['name' => 'Furniture', 'parent_id' => $category->id]);
    $listing = Listing::factory()->create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'title' => 'Local oak table',
        'country' => 'India',
        'state' => 'Karnataka',
        'city' => 'Mysuru',
        'area' => 'Vijayanagar',
    ]);
    $related = Listing::factory()->create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'city' => 'Mysuru',
        'title' => 'Local oak chair',
    ]);

    $this->get(route('listings.show', $listing))
        ->assertOk()
        ->assertSeeText($listing->title)
        ->assertSeeText($related->title)
        ->assertSeeText('Furniture')
        ->assertSeeText('India')
        ->assertSeeText('Vijayanagar')
        ->assertSeeText('Local Seller')
        ->assertSeeText('Contact seller')
        ->assertDontSeeText($seller->email)
        ->assertDontSeeText($seller->phone);
});

it('stores and emails a valid seller enquiry without exposing the seller email', function () {
    Notification::fake();

    $seller = User::factory()->create(['email' => 'seller-private@example.test']);
    $listing = Listing::factory()->create(['user_id' => $seller->id]);

    $this->post(route('listings.contact', $listing), [
        'sender_name' => 'Interested Buyer',
        'sender_email' => 'buyer@example.test',
        'message' => 'Is this listing still available?',
    ])
        ->assertRedirect(route('listings.show', $listing))
        ->assertSessionHas('status', 'Your message has been sent to the seller.');

    expect(Enquiry::query()->where('listing_id', $listing->id)->firstOrFail())
        ->sender_name->toBe('Interested Buyer')
        ->sender_email->toBe('buyer@example.test');
    Notification::assertSentOnDemand(SellerEnquiryReceived::class);

    $this->actingAs($seller)
        ->get(route('dashboard.enquiries'))
        ->assertOk()
        ->assertSeeText('Interested Buyer')
        ->assertSeeText('buyer@example.test')
        ->assertSeeText('Is this listing still available?');
});

it('uses authenticated sender details and blocks seller self-contact or inactive listings', function () {
    Notification::fake();

    $seller = User::factory()->create();
    $buyer = User::factory()->create([
        'name' => 'Signed In Buyer',
        'email' => 'signed-in@example.test',
    ]);
    $listing = Listing::factory()->create(['user_id' => $seller->id]);

    $this->actingAs($buyer)
        ->post(route('listings.contact', $listing), [
            'message' => 'I would like to know more.',
            'sender_name' => 'Spoofed Name',
            'sender_email' => 'spoofed@example.test',
        ])
        ->assertRedirect();

    $enquiry = Enquiry::query()->where('listing_id', $listing->id)->firstOrFail();
    expect($enquiry->sender_name)->toBe($buyer->name)
        ->and($enquiry->sender_email)->toBe($buyer->email);

    $this->actingAs($seller)
        ->post(route('listings.contact', $listing), ['message' => 'Can I contact myself?'])
        ->assertNotFound();

    $listing->update(['status' => 'archived']);
    $this->actingAs($buyer)
        ->post(route('listings.contact', $listing), ['message' => 'Is this active?'])
        ->assertNotFound();

    expect(Enquiry::query()->where('listing_id', $listing->id)->count())->toBe(1);
});

it('validates guest enquiry details before storing them', function () {
    $seller = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $seller->id]);

    $this->post(route('listings.contact', $listing), [
        'sender_name' => '',
        'sender_email' => 'not-an-email',
        'message' => 'Short',
    ])->assertSessionHasErrors(['sender_name', 'sender_email', 'message']);

    expect(Enquiry::query()->count())->toBe(0);
});

it('rate limits repeated contact submissions', function () {
    Notification::fake();

    $seller = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $seller->id]);

    foreach (range(1, 6) as $attempt) {
        $this->post(route('listings.contact', $listing), [
            'sender_name' => 'Buyer',
            'sender_email' => 'buyer@example.test',
            'message' => 'I would like to know more about this listing.',
        ])->assertRedirect();
    }

    $this->post(route('listings.contact', $listing), [
        'sender_name' => 'Buyer',
        'sender_email' => 'buyer@example.test',
        'message' => 'I would like to know more about this listing.',
    ])->assertTooManyRequests();

    expect(Enquiry::query()->count())->toBe(6);
});
