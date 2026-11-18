<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_storefront_renders_available_products(): void
    {
        $this->withoutVite();

        Product::factory()->create([
            'name' => 'Kopi Susu',
            'price' => 28000,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Temukan menu favoritmu')
            ->assertSeeText('Kopi Susu')
            ->assertSeeText('Pembayaran belum tersedia');
    }
}
