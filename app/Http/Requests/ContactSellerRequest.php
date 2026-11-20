<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ContactSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        if (! $listing instanceof Listing) {
            return false;
        }

        Gate::authorize('contactSeller', $listing);

        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()) {
            $this->merge([
                'sender_name' => $this->user()->name,
                'sender_email' => $this->user()->email,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
