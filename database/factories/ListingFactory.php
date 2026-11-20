<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(4, true);
        $location = fake()->randomElement([
            ['city' => 'Bengaluru', 'state' => 'Karnataka', 'area' => 'Indiranagar'],
            ['city' => 'Mangaluru', 'state' => 'Karnataka', 'area' => 'Kadri'],
            ['city' => 'Mysuru', 'state' => 'Karnataka', 'area' => 'Vijayanagar'],
            ['city' => 'Kochi', 'state' => 'Kerala', 'area' => 'Edappally'],
            ['city' => 'Hyderabad', 'state' => 'Telangana', 'area' => 'Kondapur'],
        ]);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'listing_type' => 'product',
            'title' => ucfirst($title),
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 100, 50000),
            'price_type' => 'fixed',
            'country' => 'India',
            'state' => $location['state'],
            'city' => $location['city'],
            'area' => $location['area'],
            'status' => 'active',
        ];
    }
}
