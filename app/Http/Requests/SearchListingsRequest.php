<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SearchListingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'listing_type' => ['nullable', Rule::in(['product', 'service'])],
            'category' => ['nullable', 'string', 'max:255', Rule::exists('categories', 'slug')],
            'subcategory' => ['nullable', 'string', 'max:255', Rule::exists('categories', 'slug')],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:120'],
            'min_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'max_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'price_asc', 'price_desc'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $categorySlug = $this->input('category');
                $subcategorySlug = $this->input('subcategory');

                if (
                    filled($categorySlug)
                    && filled($subcategorySlug)
                    && ! $validator->errors()->has('category')
                    && ! $validator->errors()->has('subcategory')
                ) {
                    $category = Category::query()->where('slug', $categorySlug)->first();
                    $subcategory = Category::query()->where('slug', $subcategorySlug)->first();

                    if ($category && $subcategory && $subcategory->parent_id !== $category->id) {
                        $validator->errors()->add('subcategory', 'Choose a subcategory within the selected category.');
                    }
                }

                $minPrice = $this->input('min_price');
                $maxPrice = $this->input('max_price');

                if (is_numeric($minPrice) && is_numeric($maxPrice) && (float) $maxPrice < (float) $minPrice) {
                    $validator->errors()->add('max_price', 'The maximum price must be greater than or equal to the minimum price.');
                }
            },
        ];
    }
}
