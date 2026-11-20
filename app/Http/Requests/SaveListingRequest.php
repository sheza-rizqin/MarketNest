<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $listing instanceof Listing
            ? $this->user()?->can('update', $listing) ?? false
            : $this->user()?->can('create', Listing::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('price_type') === 'contact') {
            $this->merge(['price' => null]);
        }
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('category_id')) {
                    return;
                }

                $category = Category::query()
                    ->with('parent')
                    ->find($this->input('category_id'));

                $isGroupedCategory = $category?->parent !== null
                    && $category->parent->parent_id === null
                    && in_array($category->parent->slug, ['products', 'services'], true);
                $isStandaloneCategory = $category !== null
                    && $category->parent_id === null
                    && ! in_array($category->slug, ['products', 'services'], true);

                if (! $isGroupedCategory && ! $isStandaloneCategory) {
                    $validator->errors()->add('category_id', 'Choose a category, not a category group or subcategory.');

                    return;
                }

                if ($isGroupedCategory) {
                    $expectedType = $category->parent->slug === 'products' ? 'product' : 'service';

                    if ($this->input('listing_type') !== $expectedType) {
                        $validator->errors()->add('category_id', 'Choose a category that matches the product or service type.');
                    }
                }
            },
        ];
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'subcategory_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('parent_id', $this->input('category_id')),
            ],
            'listing_type' => ['required', 'in:product,service'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'price' => [
                'required_if:price_type,fixed,negotiable',
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],
            'price_type' => ['required', 'in:fixed,negotiable,contact'],
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:120'],
            'images' => ['sometimes', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Choose a top-level category.',
            'subcategory_id.exists' => 'Choose a subcategory within the selected category.',
            'price.required_if' => 'Enter a price for fixed or negotiable pricing.',
            'images.max' => 'You can upload at most 10 images at a time.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be JPEG, PNG, or WebP files.',
            'images.*.max' => 'Each image must be 5 MB or smaller.',
        ];
    }
}
