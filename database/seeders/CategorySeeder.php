<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kopi',
                'slug' => 'kopi',
                'products' => [
                    'espresso',
                    'americano',
                    'long-black',
                    'cappuccino',
                    'cafe-latte',
                    'flat-white',
                    'mocha',
                    'kopi-susu-gula-aren',
                    'kopi-susu-pandan',
                    'caramel-latte',
                    'vanilla-latte',
                ],
            ],
            [
                'name' => 'Non-Kopi',
                'slug' => 'non-kopi',
                'products' => [
                    'matcha-latte',
                    'chocolate',
                    'red-velvet',
                ],
            ],
            [
                'name' => 'Teh',
                'slug' => 'teh',
                'products' => [
                    'lemon-tea',
                    'lychee-tea',
                    'iced-peach-tea',
                ],
            ],
            [
                'name' => 'Camilan',
                'slug' => 'camilan',
                'products' => [
                    'croissant-butter',
                    'chocolate-brownies',
                    'banana-bread',
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                ['name' => $categoryData['name']],
            );

            Product::query()
                ->whereIn('slug', $categoryData['products'])
                ->update(['category_id' => $category->id]);
        }
    }
}
