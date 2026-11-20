<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Listing;
use App\Models\Order;
use App\Services\CartSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request, CartSession $cart): RedirectResponse
    {
        $listingIds = $cart->listingIds($request->session());

        if ($listingIds === []) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty. Add a listing before checking out.',
            ]);
        }

        DB::transaction(function () use ($request, $listingIds): void {
            $buyer = $request->user();
            $listings = Listing::query()
                ->whereKey($listingIds)
                ->where('status', 'active')
                ->where('user_id', '!=', $buyer->id)
                ->lockForUpdate()
                ->get();

            if ($listings->count() !== count($listingIds)) {
                throw ValidationException::withMessages([
                    'cart' => 'One or more listings are no longer available. Review your cart and try again.',
                ]);
            }

            foreach ($listings->groupBy('user_id') as $sellerId => $sellerListings) {
                $needsSellerQuote = $sellerListings->contains(
                    fn (Listing $listing) => $listing->price_type === 'contact' || $listing->price === null,
                );
                $amountDue = $needsSellerQuote
                    ? null
                    : $sellerListings->sum(fn (Listing $listing) => (float) $listing->price);

                $order = Order::query()->create([
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                    'status' => 'pending',
                    'buyer_message' => $request->validated('buyer_message'),
                    'amount_due' => $amountDue,
                ]);

                $order->items()->createMany($sellerListings->map(fn (Listing $listing): array => [
                    'listing_id' => $listing->id,
                    'listing_title' => $listing->title,
                    'listing_type' => $listing->listing_type,
                    'price_type' => $listing->price_type,
                    'listed_price' => $listing->price,
                ])->all());
            }
        });

        $cart->clear($request->session());

        return redirect()->route('dashboard.orders')
            ->with('status', 'Your order requests were sent to the sellers.');
    }
}
