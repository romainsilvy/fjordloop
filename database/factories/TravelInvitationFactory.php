<?php

namespace Database\Factories;

use App\Models\Travel;
use App\Models\TravelInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TravelInvitation>
 */
class TravelInvitationFactory extends Factory
{
    protected $model = TravelInvitation::class;

    public function definition(): array
    {
        return [
            'travel_id' => Travel::factory(),
            'user_id' => \App\Models\User::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'token' => $this->faker->uuid(),
        ];
    }
}
