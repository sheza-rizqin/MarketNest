<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorize('favorite', $listing);

        $favorites = $request->user()->favoriteListings();

        if ($favorites->whereKey($listing->getKey())->exists()) {
            $favorites->detach($listing->getKey());
            $message = 'Removed from your saved listings.';
        } else {
            $favorites->syncWithoutDetaching([$listing->getKey()]);
            $message = 'Added to your saved listings.';
        }

        return back()->with('status', $message);
    }
}
