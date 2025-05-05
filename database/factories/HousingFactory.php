<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Housing>
 */
class HousingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isPriceByPerson = rand(0, 1) == 1;

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'url' => rand(0, 1) == 1 ? $this->faker->url() : null,
            'price_by_person' => $isPriceByPerson ? $this->faker->randomFloat(2, 0, 100) : null,
            'price_by_group' => $isPriceByPerson ? null : $this->faker->randomFloat(2, 0, 100),
            'place_name' => $this->faker->address(),
            'place_latitude' => $this->faker->latitude(),
            'place_longitude' => $this->faker->longitude(),
        ];
    }
}
