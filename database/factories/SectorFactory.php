<?php

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectorFactory extends Factory
{
    protected $model = Sector::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'keyboard_option' => $this->faker->unique()->numberBetween(1, 99),
            'greeting_message' => $this->faker->sentence(),
            'is_active' => true,
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
