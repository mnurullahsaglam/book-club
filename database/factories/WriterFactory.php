<?php

namespace Database\Factories;

use App\Models\Writer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WriterFactory extends Factory{
    protected $model = Writer::class;

    public function definition(): array
    {
        $birthDate = fake()->date();

        $deathDate = fake()->boolean() ? fake()->dateTimeBetween($birthDate) : null;

        return [
            'name' => fake()->name(),
            'image' => fake()->image(),
            'bio' => fake()->realText(),
            'birth_date' => $birthDate,
            'death_date' => $deathDate,
            'birth_place' => fake()->word(),
            'death_place' => fake()->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
