<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Travel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Travel>
 */
class TravelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'place_name' => $this->faker->city,
            'place_latitude' => $this->faker->latitude,
            'place_longitude' => $this->faker->longitude,
            'start_date' => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'end_date' => $this->faker->dateTimeBetween('+1 year', '+2 years'),
        ];
    }


    public function withOwner(User $user)
    {
        return $this->afterCreating(function (Travel $travel) use ($user) {
            $travel->members()->attach($user->id, ['is_owner' => true]);
        });
    }
}
