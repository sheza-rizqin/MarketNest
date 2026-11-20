<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;

class UpdateListingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $listing instanceof Listing
            && ($this->user()?->can('changeStatus', $listing) ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:active,sold,archived'],
        ];
    }
}
