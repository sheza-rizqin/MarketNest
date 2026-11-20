<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedListingsController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $listings = $request->user()->favoriteListings()
            ->where('listings.status', 'active')
            ->with(['category', 'subcategory', 'images'])
            ->withExists([
                'favoritedBy as is_favorited' => fn (Builder $favorites) => $favorites->whereKey($userId),
            ])
            ->orderByDesc('listings.created_at')
            ->paginate(12)
            ->withQueryString();

        return view('favorites.index', compact('listings'));
    }
}
