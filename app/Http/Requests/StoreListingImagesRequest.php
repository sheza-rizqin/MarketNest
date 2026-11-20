<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;

class StoreListingImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $listing instanceof Listing
            && ($this->user()?->can('manageImages', $listing) ?? false);
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.max' => 'You can upload at most 10 images at a time.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be JPEG, PNG, or WebP files.',
            'images.*.max' => 'Each image must be 5 MB or smaller.',
        ];
    }
}
