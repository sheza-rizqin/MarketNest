<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveListingRequest;
use App\Http\Requests\UpdateListingStatusRequest;
use App\Models\Category;
use App\Models\Listing;
use App\Services\ListingImageManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Listing::class);

        return view('listings.create', $this->listingCategoryOptions());
    }

    public function store(SaveListingRequest $request, ListingImageManager $imageManager): RedirectResponse
    {
        $this->authorize('create', Listing::class);

        $attributes = $request->validated();
        $uploads = $attributes['images'] ?? [];
        unset($attributes['images']);
        $attributes['slug'] = 'listing-'.Str::uuid();

        $listing = DB::transaction(function () use ($request, $attributes, $uploads, $imageManager): Listing {
            $listing = $request->user()->listings()->create($attributes);
            $listing->update(['slug' => $this->slugFor($attributes['title'], $listing)]);
            $imageManager->storeUploads($listing, $uploads);

            return $listing;
        });

        return redirect()->route('listings.edit', $listing)
            ->with('status', 'Listing created.');
    }

    public function edit(Listing $listing): View
    {
        $this->authorize('update', $listing);

        return view('listings.edit', [
            'listing' => $listing->load('images'),
            ...$this->listingCategoryOptions(),
        ]);
    }

    public function update(
        SaveListingRequest $request,
        Listing $listing,
        ListingImageManager $imageManager,
    ): RedirectResponse {
        $this->authorize('update', $listing);
        $attributes = $request->validated();
        $uploads = $attributes['images'] ?? [];
        unset($attributes['images']);

        if ($listing->title !== $attributes['title']) {
            $attributes['slug'] = $this->slugFor($attributes['title'], $listing);
        }

        DB::transaction(function () use ($listing, $attributes, $uploads, $imageManager): void {
            $listing->update($attributes);
            $imageManager->storeUploads($listing, $uploads);
        });

        return redirect()->route('listings.edit', $listing)
            ->with('status', 'Listing updated.');
    }

    public function updateStatus(UpdateListingStatusRequest $request, Listing $listing): RedirectResponse
    {
        $this->authorize('changeStatus', $listing);
        $listing->update($request->validated());

        return redirect()->route('dashboard.listings')->with('status', 'Listing status updated.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        $this->authorize('delete', $listing);

        $imagePaths = $listing->images()->pluck('image_path')->all();
        DB::transaction(fn () => $listing->delete());
        Storage::disk('public')->delete($imagePaths);

        return redirect()->route('dashboard.listings')->with('status', 'Listing deleted.');
    }

    private function slugFor(string $title, Listing $listing): string
    {
        return (Str::slug($title) ?: 'listing').'-'.$listing->getKey();
    }

    /**
     * @return array{categories: Collection, subcategories: Collection}
     */
    private function listingCategoryOptions(): array
    {
        $categories = Category::query()
            ->selectableForListings()
            ->with('parent')
            ->orderBy('name')
            ->get();

        $subcategories = Category::query()
            ->whereIn('parent_id', $categories->modelKeys())
            ->orderBy('name')
            ->get();

        return compact('categories', 'subcategories');
    }
}
