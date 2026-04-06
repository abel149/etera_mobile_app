<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['admin' ,'insurance','business_owner','garage','shop'];
        return [
             'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->unique()->phoneNumber(),
         
            'business_license_number' => fake()->unique()->regexify('[A-Za-z0-9]{10}'),  // Generates a 10-character alphanumeric string
    'license_expire_date' => fake()->date(),  // Generates a random date
    'tin_number' => fake()->unique()->numerify('##########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),  // Hash password once and reuse
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement($roles),  // Default role for factory
            
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
