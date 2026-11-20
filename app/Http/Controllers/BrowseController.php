<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Queries\PublicListingQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrowseController extends Controller
{
    public function categories(PublicListingQuery $listingQuery): View
    {
        $allCategories = Category::query()->orderBy('name')->get();
        $categoriesById = $allCategories->keyBy('id');

        foreach ($allCategories as $category) {
            $category->setRelation('children', collect());
        }

        foreach ($allCategories as $category) {
            if ($category->parent_id !== null && $parent = $categoriesById->get($category->parent_id)) {
                $parent->children->push($category);
            }
        }

        $counts = $listingQuery->categoryCounts();

        foreach ($allCategories as $category) {
            $category->setAttribute('active_listings_count', $counts->get($category->id, 0));
        }

        return view('browse.categories', [
            'categories' => $allCategories->whereNull('parent_id'),
        ]);
    }

    public function category(
        Request $request,
        Category $category,
        PublicListingQuery $listingQuery,
        ?Category $subcategory = null,
    ): View {
        if ($subcategory && $subcategory->parent_id !== $category->id) {
            abort(404);
        }

        $selectedCategory = $subcategory ?? $category;
        $selectedCategory->loadMissing('children');

        return $this->listingsPage(
            request: $request,
            listingQuery: $listingQuery,
            category: $selectedCategory,
            categoryParent: $subcategory ? $category : null,
            title: $selectedCategory->name.' listings',
            city: null,
        );
    }

    public function city(Request $request, string $city, PublicListingQuery $listingQuery): View
    {
        $cityName = $this->resolveCity($city);

        return $this->listingsPage(
            request: $request,
            listingQuery: $listingQuery,
            category: null,
            categoryParent: null,
            title: 'Listings in '.$cityName,
            city: $cityName,
        );
    }

    public function cityCategory(
        Request $request,
        string $city,
        Category $category,
        PublicListingQuery $listingQuery,
        ?Category $subcategory = null,
    ): View {
        $cityName = $this->resolveCity($city);

        if ($subcategory && $subcategory->parent_id !== $category->id) {
            abort(404);
        }

        $selectedCategory = $subcategory ?? $category;
        $selectedCategory->loadMissing('children');

        return $this->listingsPage(
            request: $request,
            listingQuery: $listingQuery,
            category: $selectedCategory,
            categoryParent: $subcategory ? $category : null,
            title: $selectedCategory->name.' in '.$cityName,
            city: $cityName,
        );
    }

    private function listingsPage(
        Request $request,
        PublicListingQuery $listingQuery,
        ?Category $category,
        ?Category $categoryParent,
        string $title,
        ?string $city,
    ): View {
        $listings = $listingQuery->build($category, $city, $request->user()?->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $breadcrumbs = [['label' => 'Home', 'url' => route('home')]];

        if ($city !== null) {
            $breadcrumbs[] = ['label' => $city, 'url' => route('cities.show', Str::slug($city))];
        }

        if ($category !== null) {
            if ($categoryParent !== null) {
                $breadcrumbs[] = [
                    'label' => $categoryParent->name,
                    'url' => $city
                        ? route('cities.categories.show', [Str::slug($city), $categoryParent])
                        : route('categories.show', $categoryParent),
                ];
            }

            $categoryUrl = $categoryParent
                ? ($city
                    ? route('cities.categories.show', [Str::slug($city), $categoryParent, $category])
                    : route('categories.show', [$categoryParent, $category]))
                : ($city
                    ? route('cities.categories.show', [Str::slug($city), $category])
                    : route('categories.show', $category));
            $breadcrumbs[] = ['label' => $category->name, 'url' => $categoryUrl];
        }

        $breadcrumbs[] = ['label' => $title];
        $cities = Listing::query()
            ->where('status', 'active')
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
        $categories = Category::query()->with('parent')->orderBy('name')->get();
        if ($category !== null && $category->children->isNotEmpty()) {
            $categoryCounts = $listingQuery->categoryCounts($city);

            foreach ($category->children as $child) {
                $child->setAttribute('active_listings_count', $categoryCounts->get($child->id, 0));
            }
        }

        return view('browse.listings', compact(
            'listings',
            'title',
            'breadcrumbs',
            'category',
            'categoryParent',
            'city',
            'cities',
            'categories',
        ));
    }

    private function resolveCity(string $slug): string
    {
        $city = Listing::query()
            ->where('status', 'active')
            ->select('city')
            ->distinct()
            ->get()
            ->first(fn (Listing $listing): bool => Str::slug($listing->city) === $slug);

        abort_unless($city, 404);

        return $city->city;
    }
}
