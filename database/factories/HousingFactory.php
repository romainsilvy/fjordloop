<?php

namespace Database\Factories;

use App\Models\Travel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Housing>
 */
class HousingFactory extends Factory
{
    /**
     * Default (stand-alone) attributes.
     */
    public function definition(): array
    {
        $isPriceByPerson = $this->faker->boolean();

        return [
            'name'             => $this->faker->sentence(3),
            'description'      => $this->faker->sentence(10),
            'url'              => $this->faker->boolean() ? $this->faker->url() : null,
            'price_by_person'  => $isPriceByPerson ? $this->faker->randomFloat(2, 0, 250) : null,
            'price_by_group'   => $isPriceByPerson ? null : $this->faker->randomFloat(2, 0, 500),
            'place_name'       => $this->faker->address(),
            'place_latitude'   => $this->faker->latitude(),
            'place_longitude'  => $this->faker->longitude(),
            // these will be filled by forTravel()
            'start_date'       => null,
            'start_time'       => null,
            'end_date'         => null,
            'end_time'         => null,
            'travel_id'        => null,
        ];
    }

    /**
     * Constrain this housing to fit inside a specific Travel.
     *
     * @example Housing::factory()->forTravel($trip)->create();
     */
    public function forTravel(Travel $travel): static
    {
        return $this->state(function () use ($travel) {

            // Choose a check-in moment any time during the trip
            $checkIn = $this->faker->dateTimeBetween(
                $travel->start_date,
                $travel->end_date
            );

            // Check-out must be on/after check-in but before the trip ends
            $checkOut = $this->faker->dateTimeBetween(
                $checkIn,
                $travel->end_date
            );

            return [
                'travel_id'  => $travel->id,
                'start_date' => $checkIn->format('Y-m-d'),
                'start_time' => $checkIn->format('H:i:s'),
                'end_date'   => $checkOut->format('Y-m-d'),
                'end_time'   => $checkOut->format('H:i:s'),
            ];
        });
    }
}
