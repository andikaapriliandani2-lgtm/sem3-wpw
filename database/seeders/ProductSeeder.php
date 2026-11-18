<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Espresso', 'slug' => 'espresso', 'description' => 'Kopi espresso dengan rasa yang kuat.', 'price' => 18000],
            ['name' => 'Americano', 'slug' => 'americano', 'description' => 'Espresso dengan tambahan air panas.', 'price' => 22000],
            ['name' => 'Long Black', 'slug' => 'long-black', 'description' => 'Espresso dengan air panas, rasa kopi tetap bold.', 'price' => 23000],
            ['name' => 'Cappuccino', 'slug' => 'cappuccino', 'description' => 'Espresso, susu steamed, dan foam lembut.', 'price' => 28000],
            ['name' => 'Cafe Latte', 'slug' => 'cafe-latte', 'description' => 'Espresso dengan susu steamed yang creamy.', 'price' => 30000],
            ['name' => 'Flat White', 'slug' => 'flat-white', 'description' => 'Double espresso dengan microfoam susu lembut.', 'price' => 30000],
            ['name' => 'Mocha', 'slug' => 'mocha', 'description' => 'Perpaduan espresso, cokelat, dan susu.', 'price' => 32000],
            ['name' => 'Kopi Susu Gula Aren', 'slug' => 'kopi-susu-gula-aren', 'description' => 'Kopi susu creamy dengan manis gula aren.', 'price' => 28000],
            ['name' => 'Kopi Susu Pandan', 'slug' => 'kopi-susu-pandan', 'description' => 'Kopi susu harum dengan sentuhan pandan.', 'price' => 30000],
            ['name' => 'Caramel Latte', 'slug' => 'caramel-latte', 'description' => 'Cafe latte lembut dengan sirup karamel.', 'price' => 33000],
            ['name' => 'Vanilla Latte', 'slug' => 'vanilla-latte', 'description' => 'Espresso dan susu dengan aroma vanila.', 'price' => 32000],
            ['name' => 'Matcha Latte', 'slug' => 'matcha-latte', 'description' => 'Matcha pilihan dengan susu creamy.', 'price' => 32000],
            ['name' => 'Chocolate', 'slug' => 'chocolate', 'description' => 'Minuman cokelat dengan susu hangat.', 'price' => 28000],
            ['name' => 'Red Velvet', 'slug' => 'red-velvet', 'description' => 'Minuman red velvet lembut dengan susu.', 'price' => 30000],
            ['name' => 'Lemon Tea', 'slug' => 'lemon-tea', 'description' => 'Teh segar dengan rasa lemon yang ringan.', 'price' => 20000],
            ['name' => 'Lychee Tea', 'slug' => 'lychee-tea', 'description' => 'Teh dingin segar dengan manis buah leci.', 'price' => 24000],
            ['name' => 'Iced Peach Tea', 'slug' => 'iced-peach-tea', 'description' => 'Teh dingin beraroma buah persik.', 'price' => 24000],
            ['name' => 'Croissant Butter', 'slug' => 'croissant-butter', 'description' => 'Croissant renyah dengan aroma mentega.', 'price' => 22000],
            ['name' => 'Chocolate Brownies', 'slug' => 'chocolate-brownies', 'description' => 'Brownies cokelat lembut untuk teman ngopi.', 'price' => 25000],
            ['name' => 'Banana Bread', 'slug' => 'banana-bread', 'description' => 'Roti pisang lembut, cocok untuk camilan.', 'price' => 23000],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                [...$product, 'active' => true],
            );
        }
    }
}
