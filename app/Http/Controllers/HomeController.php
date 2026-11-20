<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()
            ->whereIn('id', Listing::query()->where('status', 'active')->select('category_id')->distinct())
            ->with('parent')
            ->withCount(['listings as active_listings_count' => fn (Builder $query) => $query->where('status', 'active')])
            ->orderByDesc('active_listings_count')
            ->limit(8)
            ->get();

        $recentListings = $this->activeListings()
            ->latest()
            ->latest('id')
            ->limit(8)
            ->get();

        $featuredListing = $recentListings->first();

        $popularCities = Listing::query()
            ->where('status', 'active')
            ->select('city', DB::raw('COUNT(*) as listings_count'))
            ->groupBy('city')
            ->orderByDesc('listings_count')
            ->orderBy('city')
            ->limit(6)
            ->get();

        $cities = Listing::query()
            ->where('status', 'active')
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('home', compact(
            'categories',
            'recentListings',
            'featuredListing',
            'popularCities',
            'cities',
        ));
    }

    private function activeListings(): Builder
    {
        $query = Listing::query()
            ->where('status', 'active')
            ->with(['category', 'subcategory', 'images']);

        if (auth()->check()) {
            $userId = auth()->id();
            $query->withExists([
                'favoritedBy as is_favorited' => fn (Builder $favorites) => $favorites->whereKey($userId),
            ]);
        }

        return $query;
    }
}
