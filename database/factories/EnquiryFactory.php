<?php

namespace Database\Factories;

use App\Models\Enquiry;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'sender_name' => fake()->name(),
            'sender_email' => fake()->safeEmail(),
            'message' => fake()->paragraph(),
        ];
    }
}
