<?php

namespace Tests\Feature;

use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_product_seeder_creates_the_sample_menu_idempotently(): void
    {
        $this->seed(ProductSeeder::class);

        $this->assertDatabaseCount('products', 20);
        $this->assertDatabaseHas('products', [
            'name' => 'Kopi Susu Gula Aren',
            'slug' => 'kopi-susu-gula-aren',
            'price' => 28000,
            'active' => true,
        ]);
        $this->assertDatabaseHas('products', [
            'name' => 'Croissant Butter',
            'slug' => 'croissant-butter',
            'price' => 22000,
            'active' => true,
        ]);

        $this->seed(ProductSeeder::class);

        $this->assertDatabaseCount('products', 20);
    }
}
