<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'slug' => fake()->slug(),
            'image' =>fake()->imageUrl(),
            'page_count' => fake()->numberBetween(100, 1000),
            'publication_date' => fake()->date(),
            'publication_location' => fake()->city(),
            'is_finished' => fake()->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
