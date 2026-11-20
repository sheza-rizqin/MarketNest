<?php

namespace App\Queries;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PublicListingQuery
{
    public function build(?Category $category = null, ?string $city = null, ?int $viewerId = null): Builder
    {
        $query = Listing::query()
            ->where('status', 'active')
            ->with(['category', 'subcategory', 'images']);

        if ($category) {
            $this->applyCategoryFilter($query, $category);
        }

        if ($city !== null) {
            $query->where('city', $city);
        }

        if ($viewerId !== null) {
            $query->withExists([
                'favoritedBy as is_favorited' => fn (Builder $favorites) => $favorites->whereKey($viewerId),
            ]);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, ?int $viewerId = null): Builder
    {
        $query = $this->build(viewerId: $viewerId);

        if (filled($filters['q'] ?? null)) {
            $term = trim($filters['q']);
            $query->where(function (Builder $listings) use ($term): void {
                $listings->where('title', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%');
            });
        }

        if (filled($filters['listing_type'] ?? null)) {
            $query->where('listing_type', $filters['listing_type']);
        }

        if (filled($filters['category'] ?? null)) {
            $category = Category::query()->where('slug', $filters['category'])->firstOrFail();
            $this->applyCategoryFilter($query, $category);
        }

        if (filled($filters['subcategory'] ?? null)) {
            $subcategory = Category::query()->where('slug', $filters['subcategory'])->firstOrFail();
            $query->where(function (Builder $listings) use ($subcategory): void {
                $listings->where('subcategory_id', $subcategory->getKey())
                    ->orWhere('category_id', $subcategory->getKey());
            });
        }

        foreach (['country', 'state', 'city', 'area'] as $location) {
            if (filled($filters[$location] ?? null)) {
                $query->where($location, $filters[$location]);
            }
        }

        if (filled($filters['min_price'] ?? null)) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (filled($filters['max_price'] ?? null)) {
            $query->where('price', '<=', $filters['max_price']);
        }

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'price_asc' => $query->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END')
                ->orderBy('price')->orderByDesc('id'),
            'price_desc' => $query->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('price')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        return $query;
    }

    /**
     * @return Collection<int, int>
     */
    public function categoryCounts(?string $city = null): Collection
    {
        $categories = Category::query()->get(['id', 'parent_id'])->keyBy('id');
        $counts = $categories->mapWithKeys(fn (Category $category) => [$category->id => 0]);

        $listingCounts = Listing::query()
            ->where('status', 'active')
            ->when($city !== null, fn (Builder $listings) => $listings->where('city', $city))
            ->selectRaw('COALESCE(subcategory_id, category_id) AS leaf_category_id, COUNT(*) AS listings_count')
            ->groupBy('leaf_category_id')
            ->get();

        foreach ($listingCounts as $listingCount) {
            $categoryId = (int) $listingCount->leaf_category_id;

            while ($category = $categories->get($categoryId)) {
                $counts[$categoryId] += (int) $listingCount->listings_count;

                if ($category->parent_id === null) {
                    break;
                }

                $categoryId = (int) $category->parent_id;
            }
        }

        return $counts;
    }

    /**
     * @return array<int, int>
     */
    private function categoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->getKey()];
        $frontier = $ids;

        while ($frontier !== []) {
            $frontier = Category::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            $ids = [...$ids, ...$frontier];
        }

        return $ids;
    }

    private function applyCategoryFilter(Builder $query, Category $category): void
    {
        $categoryIds = $this->categoryAndDescendantIds($category);

        $query->where(function (Builder $listings) use ($categoryIds): void {
            $listings->whereIn('category_id', $categoryIds)
                ->orWhereIn('subcategory_id', $categoryIds);
        });
    }
}
