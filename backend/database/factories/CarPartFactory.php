<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CarPart>
 */
class CarPartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'component' => $this->faker->words(1, true),
            'number' => $this->faker->regexify('[A-Z]{2}[0-9]{6}'),
            'grade' => $this->faker->randomElement(['A', 'B', 'C']),
        ];
    }
}
