<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderListingImageRequest;
use App\Http\Requests\StoreListingImagesRequest;
use App\Models\Listing;
use App\Services\ListingImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListingImageController extends Controller
{
    public function show(Request $request, Listing $listing, int $image): StreamedResponse
    {
        $this->authorize('viewImage', $listing);

        $listingImage = $listing->images()->findOrFail($image);

        return Storage::disk('public')->response($listingImage->image_path, headers: [
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function store(
        StoreListingImagesRequest $request,
        Listing $listing,
        ListingImageManager $imageManager,
    ): RedirectResponse {
        $this->authorize('manageImages', $listing);
        $imageManager->storeUploads($listing, $request->validated('images'));

        return redirect()->route('listings.edit', $listing)->with('status', 'Photos uploaded.');
    }

    public function setPrimary(Listing $listing, int $image, ListingImageManager $imageManager): RedirectResponse
    {
        $this->authorize('manageImages', $listing);

        $listingImage = $listing->images()->findOrFail($image);
        $imageManager->setPrimary($listing, $listingImage);

        return redirect()->route('listings.edit', $listing)->with('status', 'Primary photo updated.');
    }

    public function reorder(
        ReorderListingImageRequest $request,
        Listing $listing,
        int $image,
        ListingImageManager $imageManager,
    ): RedirectResponse {
        $this->authorize('manageImages', $listing);

        $listingImage = $listing->images()->findOrFail($image);
        $moved = $imageManager->move($listing, $listingImage, $request->validated('direction'));

        return redirect()->route('listings.edit', $listing)->with(
            'status',
            $moved ? 'Photo order updated.' : 'Photo is already at that end of the list.',
        );
    }

    public function destroy(Listing $listing, int $image, ListingImageManager $imageManager): RedirectResponse
    {
        $this->authorize('manageImages', $listing);

        $listingImage = $listing->images()->findOrFail($image);
        $imageManager->delete($listing, $listingImage);

        return redirect()->route('listings.edit', $listing)->with('status', 'Photo removed.');
    }
}
