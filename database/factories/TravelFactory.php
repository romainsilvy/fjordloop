<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Travel;
use App\Models\Housing;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon as SupportCarbon;   // alias to avoid confusion

/**
 * @extends Factory<\App\Models\Travel>
 */
class TravelFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 year', '+1 year');
        $length = $this->faker->numberBetween(3, 20);

        return [
            'name'            => $this->faker->sentence(3),
            'place_name'      => $this->faker->city,
            'place_latitude'  => $this->faker->latitude,
            'place_longitude' => $this->faker->longitude,
            'start_date'      => $start,
            'end_date'        => (clone $start)->modify("+{$length} days"),
        ];
    }

    /** Attach an owner after the trip is created. */
    public function withOwner(User $user): static
    {
        return $this->afterCreating(function (Travel $travel) use ($user) {
            $travel->attachOwner($user);
        });
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------  HOUSINGS  --------------------------------- */
    /* --------------------------------------------------------------------- */

    /** Cover every night of the trip with at least one Housing. */
    public function createHousings(): static
    {
        return $this->afterCreating(function (Travel $travel) {
            $cursor = SupportCarbon::parse($travel->start_date)->startOfDay();

            $tripEnd = SupportCarbon::parse($travel->end_date)->endOfDay();
            $housings = [];

            // Ensure coverage day-by-day, with 1–3 consecutive nights for each stay
            while ($cursor->lte($tripEnd)) {
                $nights = rand(1, 3);
                $checkout = (clone $cursor)->addDays($nights)->min($tripEnd);

                $checkIn  = (clone $cursor)->setTime(15, 0, 0);
                $checkOut = (clone $checkout)->setTime(11, 0, 0);

                $housings[] = [
                    'travel_id'   => $travel->id,
                    'name'        => fake()->company . ' Hotel',
                    'description' => fake()->sentence(10),
                    'place_name'  => fake()->address,
                    'place_latitude'  => fake()->latitude,
                    'place_longitude' => fake()->longitude,
                    'price_by_group'  => fake()->randomFloat(2, 50, 500),
                    'start_date'  => $checkIn->toDateString(),
                    'start_time'  => $checkIn->toTimeString(),
                    'end_date'    => $checkOut->toDateString(),
                    'end_time'    => $checkOut->toTimeString(),
                ];

                $cursor = (clone $checkout)->addDay()->startOfDay();
            }

            Housing::factory()->createMany($housings);
        });
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------  ACTIVITIES  -------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * Create 1–5 activities **per calendar day** of the trip.
     *
     * @param  int  $min  minimal daily activities (default 1)
     * @param  int  $max  maximal daily activities (default 5)
     */
    public function createActivities(int $min = 1, int $max = 5): static
    {
        return $this->afterCreating(function (Travel $travel) use ($min, $max) {
            $period = SupportCarbon::parse($travel->start_date)
                ->daysUntil(SupportCarbon::parse($travel->end_date));

            foreach ($period as $day) {
                $dailyCount = rand($min, $max);
                $activities = [];

                // Generate activities for this day, start between 07:00 and 20:00 and lasting 30–180 minutes
                for ($i = 0; $i < $dailyCount; $i++) {
                    $start = (clone $day)->setTime(7, 0)->addMinutes(rand(0, 13 * 60));
                    $end   = (clone $start)->addMinutes(rand(30, 180));

                    $activities[] = [
                        'travel_id'   => $travel->id,
                        'name'        => fake()->sentence(3),
                        'description' => fake()->sentence(10),
                        'place_name'  => fake()->address,
                        'place_latitude'  => fake()->latitude,
                        'place_longitude' => fake()->longitude,
                        'price_by_group'  => fake()->randomFloat(2, 0, 200),
                        'start_date'  => $start->toDateString(),
                        'start_time'  => $start->toTimeString(),
                        'end_date'    => $end->toDateString(),
                        'end_time'    => $end->toTimeString(),
                    ];
                }

                Activity::factory()->createMany($activities);
            }
        });
    }
}
