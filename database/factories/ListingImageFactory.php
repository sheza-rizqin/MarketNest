<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingImage>
 */
class ListingImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'image_path' => 'listings/'.$this->faker->uuid().'.jpg',
            'is_primary' => false,
            'sort_order' => 0,
        ];
    }
}
