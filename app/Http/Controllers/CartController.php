<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartItemRequest;
use App\Models\Listing;
use App\Services\CartSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request, CartSession $cart): View
    {
        $listingIds = $cart->listingIds($request->session());
        $listings = Listing::query()
            ->with('images')
            ->whereKey($listingIds)
            ->get()
            ->keyBy('id');
        $missingIds = array_values(array_diff($listingIds, $listings->modelKeys()));

        foreach ($missingIds as $missingId) {
            $cart->remove($request->session(), $missingId);
        }

        $listingIds = array_values(array_diff($listingIds, $missingIds));
        $hasUnavailableItems = $listings->contains(
            fn (Listing $listing) => $listing->status !== 'active' || $request->user()?->id === $listing->user_id,
        );
        $needsSellerQuote = $listings->contains(
            fn (Listing $listing) => $listing->price_type === 'contact' || $listing->price === null,
        );
        $subtotal = $needsSellerQuote
            ? null
            : $listings->sum(fn (Listing $listing) => (float) $listing->price);

        return view('cart.index', [
            'listings' => $listings,
            'listingIds' => $listingIds,
            'hasUnavailableItems' => $hasUnavailableItems,
            'needsSellerQuote' => $needsSellerQuote,
            'subtotal' => $subtotal,
            'removedMissingItems' => $missingIds !== [],
        ]);
    }

    public function store(AddCartItemRequest $request, Listing $listing, CartSession $cart): RedirectResponse
    {
        $added = $cart->add($request->session(), $listing->id);

        return redirect()->route('cart.index')->with(
            'status',
            $added ? 'Listing added to your cart.' : 'This listing is already in your cart.',
        );
    }

    public function destroy(Request $request, int $listingId, CartSession $cart): RedirectResponse
    {
        $cart->remove($request->session(), $listingId);

        return redirect()->route('cart.index')->with('status', 'Listing removed from your cart.');
    }
}
