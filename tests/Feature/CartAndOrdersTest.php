<?php

use App\Models\Listing;
use App\Models\Order;
use App\Models\User;

it('adds active products and services to the cart once and blocks own or inactive listings', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $product = Listing::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Used bicycle',
        'listing_type' => 'product',
    ]);
    $service = Listing::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Garden maintenance',
        'listing_type' => 'service',
    ]);
    $inactive = Listing::factory()->create([
        'user_id' => $seller->id,
        'status' => 'archived',
    ]);

    $this->actingAs($buyer)
        ->post(route('cart.items.store', $product))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('status', 'Listing added to your cart.');

    $this->post(route('cart.items.store', $service))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('status', 'Listing added to your cart.');

    $this->post(route('cart.items.store', $product))
        ->assertSessionHas('status', 'This listing is already in your cart.');

    $this->post(route('cart.items.store', $inactive))->assertNotFound();

    $this->actingAs($seller)
        ->post(route('cart.items.store', $product))
        ->assertNotFound();

    $this->actingAs($buyer)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSeeText('Used bicycle')
        ->assertSeeText('Garden maintenance')
        ->assertDontSeeText('This listing is no longer active.');
});

it('creates separate unpaid requests per seller and preserves listing snapshots', function () {
    $buyer = User::factory()->create();
    $firstSeller = User::factory()->create();
    $secondSeller = User::factory()->create();
    $product = Listing::factory()->create([
        'user_id' => $firstSeller->id,
        'title' => 'Camera body',
        'listing_type' => 'product',
        'price' => 12000,
        'price_type' => 'fixed',
    ]);
    $service = Listing::factory()->create([
        'user_id' => $firstSeller->id,
        'title' => 'Home cleaning',
        'listing_type' => 'service',
        'price' => 2500,
        'price_type' => 'negotiable',
    ]);
    $quoteListing = Listing::factory()->create([
        'user_id' => $secondSeller->id,
        'title' => 'Custom repair',
        'listing_type' => 'service',
        'price' => null,
        'price_type' => 'contact',
    ]);

    $this->post(route('cart.items.store', $product));
    $this->post(route('cart.items.store', $service));
    $this->post(route('cart.items.store', $quoteListing));

    $this->actingAs($buyer)
        ->post(route('checkout.store'), ['buyer_message' => 'Please contact me after 5 PM.'])
        ->assertRedirect(route('dashboard.orders'))
        ->assertSessionHas('status', 'Your order requests were sent to the sellers.')
        ->assertSessionMissing('cart_listing_ids');

    $orders = Order::query()->with('items')->orderBy('seller_id')->get();

    expect($orders)->toHaveCount(2)
        ->and($orders->first()->status)->toBe('pending')
        ->and($orders->first()->amount_due)->toBe('14500.00')
        ->and($orders->first()->items)->toHaveCount(2)
        ->and($orders->last()->amount_due)->toBeNull()
        ->and($orders->last()->items->first()->listing_title)->toBe('Custom repair')
        ->and($orders->first()->buyer_message)->toBe('Please contact me after 5 PM.');

    $this->get(route('dashboard.orders'))
        ->assertOk()
        ->assertSeeText('Camera body')
        ->assertSeeText('Custom repair')
        ->assertSeeText('do not take payment');

    $this->actingAs($firstSeller)
        ->get(route('dashboard.sales'))
        ->assertOk()
        ->assertSeeText('Camera body')
        ->assertSeeText('Home cleaning')
        ->assertDontSeeText('Custom repair')
        ->assertSeeText($buyer->name);
});

it('rejects stale cart items and preserves them for removal or review', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $seller->id]);

    $this->post(route('cart.items.store', $listing));
    $listing->update(['status' => 'sold']);

    $this->actingAs($buyer)
        ->from(route('cart.index'))
        ->post(route('checkout.store'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors('cart')
        ->assertSessionHas('cart_listing_ids', [$listing->id]);

    expect(Order::query()->count())->toBe(0);
});

it('enforces seller status transitions and prevents buyers or other sellers from changing orders', function () {
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $otherBuyer = User::factory()->create();
    $otherSeller = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $seller->id]);

    $this->post(route('cart.items.store', $listing));
    $this->actingAs($buyer)->post(route('checkout.store'));
    $order = Order::query()->firstOrFail();

    $this->actingAs($otherSeller)
        ->patch(route('dashboard.sales.status', $order), ['status' => 'accepted'])
        ->assertForbidden();
    $this->actingAs($otherBuyer)
        ->patch(route('dashboard.orders.cancel', $order))
        ->assertForbidden();
    $this->actingAs($buyer)
        ->get(route('dashboard.orders'))
        ->assertOk()
        ->assertSeeText($listing->title);
    $this->actingAs($otherBuyer)
        ->get(route('dashboard.orders'))
        ->assertOk()
        ->assertDontSeeText($listing->title);

    $this->actingAs($seller)
        ->patch(route('dashboard.sales.status', $order), ['status' => 'accepted'])
        ->assertRedirect(route('dashboard.sales'))
        ->assertSessionHas('status', 'Order request accepted.');

    $this->actingAs($buyer)
        ->patch(route('dashboard.orders.cancel', $order))
        ->assertForbidden();

    $this->actingAs($seller)
        ->patch(route('dashboard.sales.status', $order), ['status' => 'completed'])
        ->assertRedirect(route('dashboard.sales'))
        ->assertSessionHas('status', 'Order request marked as completed.');

    expect($order->fresh()->status)->toBe('completed');
});

it('lets the buyer cancel a pending request and prevents the seller from cancelling it as buyer', function () {
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $listing = Listing::factory()->create(['user_id' => $seller->id]);

    $this->post(route('cart.items.store', $listing));
    $this->actingAs($buyer)->post(route('checkout.store'));
    $order = Order::query()->firstOrFail();

    $this->actingAs($seller)
        ->patch(route('dashboard.orders.cancel', $order))
        ->assertForbidden();

    $this->actingAs($buyer)
        ->patch(route('dashboard.orders.cancel', $order))
        ->assertRedirect(route('dashboard.orders'))
        ->assertSessionHas('status', 'Order request cancelled.');

    expect($order->fresh()->status)->toBe('cancelled');
});
