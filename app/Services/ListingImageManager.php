<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ListingImageManager
{
    public function storeUploads(Listing $listing, array $uploads): void
    {
        if ($uploads === []) {
            return;
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use ($listing, $uploads, &$storedPaths): void {
                $listing->newQuery()
                    ->whereKey($listing->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $images = $listing->images()->lockForUpdate()->get();

                if ($images->count() + count($uploads) > 10) {
                    throw ValidationException::withMessages([
                        'images' => 'A listing can have at most 10 images.',
                    ]);
                }

                $sortOrder = (int) ($images->max('sort_order') ?? -1) + 1;
                $hasPrimary = $images->contains('is_primary', true);

                foreach ($uploads as $upload) {
                    if (! $upload instanceof UploadedFile) {
                        throw new RuntimeException('An uploaded listing image was not a valid file.');
                    }

                    $path = $upload->store('listings', 'public');

                    if (! is_string($path) || $path === '') {
                        throw new RuntimeException('A listing image could not be stored.');
                    }

                    $storedPaths[] = $path;

                    $listing->images()->create([
                        'image_path' => $path,
                        'is_primary' => ! $hasPrimary,
                        'sort_order' => $sortOrder++,
                    ]);

                    $hasPrimary = true;
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }
    }

    public function setPrimary(Listing $listing, ListingImage $image): void
    {
        $ownedImage = $listing->images()->findOrFail($image->getKey());

        DB::transaction(function () use ($listing, $ownedImage): void {
            $listing->images()
                ->whereKeyNot($ownedImage->getKey())
                ->update(['is_primary' => false]);

            $ownedImage->update(['is_primary' => true]);
        });
    }

    public function move(Listing $listing, ListingImage $image, string $direction): bool
    {
        $images = $listing->images()->get();
        $currentIndex = $images->search(fn (ListingImage $item): bool => $item->is($image));
        $targetIndex = $currentIndex + ($direction === 'up' ? -1 : 1);

        if ($currentIndex === false || ! $images->has($targetIndex)) {
            return false;
        }

        $items = $images->all();
        [$items[$currentIndex], $items[$targetIndex]] = [$items[$targetIndex], $items[$currentIndex]];

        DB::transaction(function () use ($items): void {
            foreach ($items as $sortOrder => $item) {
                $item->update(['sort_order' => $sortOrder]);
            }
        });

        return true;
    }

    public function delete(Listing $listing, ListingImage $image): void
    {
        $ownedImage = $listing->images()->findOrFail($image->getKey());
        $wasPrimary = $ownedImage->is_primary;
        $path = $ownedImage->image_path;

        DB::transaction(function () use ($listing, $ownedImage, $wasPrimary): void {
            $ownedImage->delete();

            /** @var Collection<int, ListingImage> $remainingImages */
            $remainingImages = $listing->images()->get();

            if ($wasPrimary && $remainingImages->isNotEmpty()) {
                $remainingImages->first()->update(['is_primary' => true]);
            }

            foreach ($remainingImages as $sortOrder => $remainingImage) {
                $remainingImage->update(['sort_order' => $sortOrder]);
            }
        });

        Storage::disk('public')->delete($path);
    }
}
