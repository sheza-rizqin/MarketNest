<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = [
            User::firstOrCreate(
                ['email' => 'ananya.seller@marketnest.test'],
                [
                    'name' => 'Ananya Rao',
                    'password' => Hash::make('MarketNestDemo!2026'),
                    'email_verified_at' => now(),
                ],
            ),
            User::firstOrCreate(
                ['email' => 'rahul.seller@marketnest.test'],
                [
                    'name' => 'Rahul Menon',
                    'password' => Hash::make('MarketNestDemo!2026'),
                    'email_verified_at' => now(),
                ],
            ),
        ];

        User::firstOrCreate(
            ['email' => 'buyer@marketnest.test'],
            [
                'name' => 'Aarav Iyer',
                'password' => Hash::make('MarketNestDemo!2026'),
                'email_verified_at' => now(),
            ],
        );

        $categories = [];

        foreach (['products' => 'Products', 'services' => 'Services'] as $slug => $name) {
            $categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'parent_id' => null],
            );
        }

        $categoryDefinitions = [
            'electronics' => ['Electronics', 'products'],
            'smartphones' => ['Smartphones', 'electronics'],
            'laptops' => ['Laptops & Computers', 'electronics'],
            'cameras' => ['Cameras', 'electronics'],
            'home-living' => ['Home & Living', 'products'],
            'furniture' => ['Furniture', 'home-living'],
            'home-decor' => ['Home Decor', 'home-living'],
            'fashion' => ['Fashion', 'products'],
            'clothing' => ['Clothing & Textiles', 'fashion'],
            'jewellery' => ['Jewellery', 'fashion'],
            'outdoors' => ['Sports & Outdoors', 'products'],
            'bicycles' => ['Bicycles', 'outdoors'],
            'reading' => ['Books & Learning', 'products'],
            'books-media' => ['Books & Study Materials', 'reading'],
            'home-services' => ['Home Services', 'services'],
            'cleaning' => ['Cleaning', 'home-services'],
            'appliance-repair' => ['Appliance Repair', 'home-services'],
            'learning-services' => ['Learning & Coaching', 'services'],
            'tutoring' => ['Tutoring', 'learning-services'],
            'music-lessons' => ['Music Lessons', 'learning-services'],
            'professional-services' => ['Professional Services', 'services'],
            'photography' => ['Photography', 'professional-services'],
            'web-design' => ['Web Design', 'professional-services'],
            'translation' => ['Translation', 'professional-services'],
        ];

        foreach ($categoryDefinitions as $slug => [$name, $parentSlug]) {
            $categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'parent_id' => $categories[$parentSlug]->id],
            );
        }

        $listings = [
            [
                'title' => 'Google Pixel 6a, 128GB',
                'description' => 'Unlocked Pixel 6a in good working condition with a clear screen, original box and USB cable. Battery lasts through a full day of regular use.',
                'listing_type' => 'product',
                'category' => 'smartphones',
                'price' => 14500,
                'price_type' => 'negotiable',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'area' => 'Koramangala',
            ],
            [
                'title' => 'Lenovo ThinkPad T480 laptop',
                'description' => 'Business laptop with Intel Core i5, 16GB RAM and a 512GB SSD. Keyboard, ports and webcam work well; includes the original charger.',
                'listing_type' => 'product',
                'category' => 'laptops',
                'price' => 24000,
                'price_type' => 'fixed',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'area' => 'Indiranagar',
            ],
            [
                'title' => 'Solid wood coffee table',
                'description' => 'Compact solid wood coffee table with a natural finish. Sturdy and well maintained, with minor surface marks from normal use.',
                'listing_type' => 'product',
                'category' => 'furniture',
                'price' => 6800,
                'price_type' => 'negotiable',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'area' => 'Jayanagar',
            ],
            [
                'title' => 'Two-bedroom home deep cleaning',
                'description' => 'A detailed home cleaning service covering floors, kitchen surfaces, bathrooms and reachable windows. Cleaning supplies are included.',
                'listing_type' => 'service',
                'category' => 'cleaning',
                'price' => 1800,
                'price_type' => 'fixed',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'area' => 'Whitefield',
            ],
            [
                'title' => 'CBSE mathematics tutoring',
                'description' => 'One-to-one mathematics lessons for classes 8 to 10, with topic revision and practice problems. Sessions are available on weekday evenings.',
                'listing_type' => 'service',
                'category' => 'tutoring',
                'price' => 650,
                'price_type' => 'fixed',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'area' => 'HSR Layout',
            ],
            [
                'title' => 'Giant Escape 3 city bicycle',
                'description' => 'Well-kept hybrid bicycle with 700c wheels, working gears and recently replaced brake pads. A comfortable option for commuting around town.',
                'listing_type' => 'product',
                'category' => 'bicycles',
                'price' => 12500,
                'price_type' => 'negotiable',
                'city' => 'Mangaluru',
                'state' => 'Karnataka',
                'area' => 'Kadri',
            ],
            [
                'title' => 'Handloom cotton-silk saree',
                'description' => 'A lightweight handloom cotton-silk saree in a deep blue colorway with a simple woven border. Unused and stored in a clean, dry cupboard.',
                'listing_type' => 'product',
                'category' => 'clothing',
                'price' => 3400,
                'price_type' => 'fixed',
                'city' => 'Mangaluru',
                'state' => 'Karnataka',
                'area' => 'Bejai',
            ],
            [
                'title' => 'Beginner acoustic guitar lessons',
                'description' => 'Patient beginner lessons covering tuning, basic chords and simple songs. Bring your own acoustic guitar; lesson notes are provided.',
                'listing_type' => 'service',
                'category' => 'music-lessons',
                'price' => 500,
                'price_type' => 'fixed',
                'city' => 'Mangaluru',
                'state' => 'Karnataka',
                'area' => 'Kankanady',
            ],
            [
                'title' => 'Handwoven coir table runner',
                'description' => 'Natural coir table runner handwoven by a local craft group. Measures approximately 150 cm and is suitable for indoor use.',
                'listing_type' => 'product',
                'category' => 'home-decor',
                'price' => 1100,
                'price_type' => 'fixed',
                'city' => 'Mangaluru',
                'state' => 'Karnataka',
                'area' => 'Bolar',
            ],
            [
                'title' => 'Engineering exam book bundle',
                'description' => 'A set of four engineering mathematics and mechanics textbooks in good condition. Some pages have neat pencil notes; no missing chapters.',
                'listing_type' => 'product',
                'category' => 'books-media',
                'price' => 1600,
                'price_type' => 'negotiable',
                'city' => 'Mysuru',
                'state' => 'Karnataka',
                'area' => 'Kuvempunagar',
            ],
            [
                'title' => 'Canon EOS 200D with kit lens',
                'description' => 'Canon EOS 200D DSLR with 18-55mm kit lens, one battery and charger. Shutter and autofocus work properly; available for an in-person check.',
                'listing_type' => 'product',
                'category' => 'cameras',
                'price' => 28500,
                'price_type' => 'negotiable',
                'city' => 'Mysuru',
                'state' => 'Karnataka',
                'area' => 'Vijayanagar',
            ],
            [
                'title' => 'At-home family portrait session',
                'description' => 'A relaxed one-hour family portrait session in your home or garden, with edited digital images delivered in a private gallery.',
                'listing_type' => 'service',
                'category' => 'photography',
                'price' => null,
                'price_type' => 'contact',
                'city' => 'Mysuru',
                'state' => 'Karnataka',
                'area' => 'Lakshmipuram',
            ],
            [
                'title' => 'Washing machine home repair',
                'description' => 'Home diagnosis and repair for common washing machine issues, including drainage and spin-cycle problems. Parts, if required, are quoted separately.',
                'listing_type' => 'service',
                'category' => 'appliance-repair',
                'price' => 450,
                'price_type' => 'fixed',
                'city' => 'Mysuru',
                'state' => 'Karnataka',
                'area' => 'Saraswathipuram',
            ],
            [
                'title' => 'OnePlus Nord CE 3 Lite, 128GB',
                'description' => 'Unlocked OnePlus Nord CE 3 Lite with 8GB RAM, original charger and protective case. Screen is scratch-free and all buttons work.',
                'listing_type' => 'product',
                'category' => 'smartphones',
                'price' => 12500,
                'price_type' => 'negotiable',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'area' => 'Edappally',
            ],
            [
                'title' => 'Pair of rosewood bedside tables',
                'description' => 'Two matching bedside tables with drawers and a warm rosewood finish. Both are stable and have smoothly working drawer runners.',
                'listing_type' => 'product',
                'category' => 'furniture',
                'price' => 7200,
                'price_type' => 'fixed',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'area' => 'Kakkanad',
            ],
            [
                'title' => 'Move-in cleaning for a two-bedroom flat',
                'description' => 'Move-in cleaning for an empty two-bedroom home, including floors, kitchen cabinets, bathrooms and interior glass. Please share the home size when enquiring.',
                'listing_type' => 'service',
                'category' => 'cleaning',
                'price' => 3000,
                'price_type' => 'fixed',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'area' => 'Palarivattom',
            ],
            [
                'title' => 'Malayalam to English document translation',
                'description' => 'Careful Malayalam-to-English translation for personal letters and general documents. Final pricing depends on document length and formatting.',
                'listing_type' => 'service',
                'category' => 'translation',
                'price' => 900,
                'price_type' => 'negotiable',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'area' => 'Fort Kochi',
            ],
            [
                'title' => 'Dell Latitude 5420 business laptop',
                'description' => 'Latitude 5420 with Intel Core i5, 16GB RAM and 512GB SSD. Includes charger; webcam, Wi-Fi and keyboard have been tested.',
                'listing_type' => 'product',
                'category' => 'laptops',
                'price' => 31000,
                'price_type' => 'fixed',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'area' => 'Kondapur',
            ],
            [
                'title' => 'iPhone 12, 128GB',
                'description' => 'Unlocked iPhone 12 in good cosmetic condition with working Face ID, cameras and speakers. Includes a charging cable and protective case.',
                'listing_type' => 'product',
                'category' => 'smartphones',
                'price' => 26000,
                'price_type' => 'negotiable',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'area' => 'Miyapur',
            ],
            [
                'title' => 'Small business website setup',
                'description' => 'A responsive five-page website for a local business, including contact details, service information and basic on-page search setup. Hosting is quoted separately.',
                'listing_type' => 'service',
                'category' => 'web-design',
                'price' => null,
                'price_type' => 'contact',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'area' => 'Gachibowli',
            ],
            [
                'title' => 'Class 11 and 12 physics tutoring',
                'description' => 'Concept-focused physics tutoring for classes 11 and 12, with worked examples and exam preparation. Online or in-person sessions by arrangement.',
                'listing_type' => 'service',
                'category' => 'tutoring',
                'price' => 700,
                'price_type' => 'fixed',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'area' => 'Ameerpet',
            ],
            [
                'title' => 'Split AC maintenance visit',
                'description' => 'Routine split AC inspection and filter cleaning at home. Any refrigerant refill or replacement parts are discussed and priced before work begins.',
                'listing_type' => 'service',
                'category' => 'appliance-repair',
                'price' => 650,
                'price_type' => 'fixed',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'area' => 'Madhapur',
            ],
            [
                'title' => 'Handmade silver stud earrings',
                'description' => 'A pair of simple handmade 925 silver stud earrings with a smooth finish. Comes in a small reusable storage pouch.',
                'listing_type' => 'product',
                'category' => 'jewellery',
                'price' => 2300,
                'price_type' => 'fixed',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'area' => 'Basavanagudi',
            ],
            [
                'title' => 'Product photography for small shops',
                'description' => 'A compact product photography session for local makers and small shops, with simple lighting and edited web-ready images. Scope is agreed before booking.',
                'listing_type' => 'service',
                'category' => 'photography',
                'price' => null,
                'price_type' => 'contact',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'area' => 'Malleshwaram',
            ],
            [
                'title' => 'Set of handmade coir storage baskets',
                'description' => 'Three nesting storage baskets woven from natural coir. Suitable for dry indoor storage and made by a small local craft workshop.',
                'listing_type' => 'product',
                'category' => 'home-decor',
                'price' => 1350,
                'price_type' => 'fixed',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'area' => 'Tripunithura',
            ],
        ];

        foreach ($listings as $index => $listing) {
            $slug = Str::slug($listing['title']);
            $subcategory = $categories[$listing['category']];
            unset($listing['category']);

            Listing::updateOrCreate(
                ['slug' => $slug],
                [
                    ...$listing,
                    'user_id' => $sellers[$index % count($sellers)]->id,
                    'category_id' => $subcategory->parent_id,
                    'subcategory_id' => $subcategory->id,
                    'slug' => $slug,
                    'country' => 'India',
                    'status' => 'active',
                ],
            );
        }
    }
}
