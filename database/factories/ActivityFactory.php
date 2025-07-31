<?php

namespace Database\Factories;

use App\Models\Travel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Default attributes (when you don’t care about dates yet).
     */
    public function definition(): array
    {
        $isPriceByPerson = $this->faker->boolean();

        return [
            'name'             => $this->faker->sentence(3),
            'description'      => $this->faker->sentence(10),
            'url'              => $this->faker->boolean() ? $this->faker->url() : null,
            'price_by_person'  => $isPriceByPerson ? $this->faker->randomFloat(2, 0, 100) : null,
            'price_by_group'   => $isPriceByPerson ? null : $this->faker->randomFloat(2, 0, 100),
            'place_name'       => $this->faker->address(),
            'place_latitude'   => $this->faker->latitude(),
            'place_longitude'  => $this->faker->longitude(),
            // date/time fields kept null here – they’ll be filled by forTravel()
            'start_date'       => null,
            'start_time'       => null,
            'end_date'         => null,
            'end_time'         => null,
            'travel_id'        => null,
        ];
    }

    /**
     * Constrain the activity so its datetime range fits inside the given Travel.
     */
    public function forTravel(Travel $travel): static
    {
        return $this->state(function () use ($travel) {
            // Pick a start moment somewhere during the trip
            $start = $this->faker->dateTimeBetween(
                $travel->start_date,
                $travel->end_date
            );

            // End must be on/after the start but still before the trip ends
            $end   = $this->faker->dateTimeBetween(
                $start,
                $travel->end_date
            );

            return [
                'travel_id'  => $travel->id,
                'start_date' => $start->format('Y-m-d'),
                'start_time' => $start->format('H:i:s'),
                'end_date'   => $end->format('Y-m-d'),
                'end_time'   => $end->format('H:i:s'),
            ];
        });
    }
}
