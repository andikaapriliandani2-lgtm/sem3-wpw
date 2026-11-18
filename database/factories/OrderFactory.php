<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => 'KOPI-'.fake()->unique()->bothify('########'),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'status' => 'pending',
            'payment_method' => 'cash',
            'subtotal' => 28000,
            'discount' => 0,
            'total' => 28000,
        ];
    }
}
