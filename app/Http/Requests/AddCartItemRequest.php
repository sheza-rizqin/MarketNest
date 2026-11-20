<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        if (! $listing instanceof Listing) {
            return false;
        }

        Gate::authorize('purchase', $listing);

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
