<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchListingsRequest;
use App\Models\Category;
use App\Models\Listing;
use App\Queries\PublicListingQuery;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicListingController extends Controller
{
    public function index(SearchListingsRequest $request, PublicListingQuery $listingQuery): View
    {
        $listings = $listingQuery->search($request->validated(), $request->user()?->id)
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->with('parent')->orderBy('name')->get();
        $cities = Listing::query()
            ->where('status', 'active')
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('listings.index', compact('listings', 'categories', 'cities'));
    }

    public function show(Listing $listing, PublicListingQuery $listingQuery): View
    {
        $this->authorize('view', $listing);

        $listing->load(['category', 'subcategory', 'images', 'user:id,name']);
        $relatedCategory = $listing->subcategory ?? $listing->category;
        $relatedListings = $listingQuery->build($relatedCategory, $listing->city, auth()->id())
            ->whereKeyNot($listing->getKey())
            ->latest()
            ->limit(4)
            ->get();

        if ($relatedListings->count() < 4) {
            $additionalListings = $listingQuery->build($relatedCategory, null, auth()->id())
                ->whereKeyNot($listing->getKey())
                ->whereNotIn('id', $relatedListings->modelKeys())
                ->latest()
                ->limit(4 - $relatedListings->count())
                ->get();

            $relatedListings = $relatedListings->concat($additionalListings);
        }

        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => $listing->category->name, 'url' => route('categories.show', $listing->category)],
        ];

        if ($listing->subcategory) {
            $breadcrumbs[] = [
                'label' => $listing->subcategory->name,
                'url' => route('categories.show', [$listing->category, $listing->subcategory]),
            ];
        }

        $breadcrumbs[] = ['label' => $listing->city, 'url' => route('cities.show', Str::slug($listing->city))];
        $breadcrumbs[] = ['label' => $listing->title];

        return view('listings.show', compact('listing', 'relatedListings', 'breadcrumbs'));
    }
}
