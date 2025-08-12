<?php

use App\Livewire\MonthCalendar;
use App\Models\Activity;
use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

function createMonthCalendarComponent($travel = null)
{
    $user = User::factory()->create();
    auth()->login($user);

    if (! $travel) {
        $travel = Travel::factory()->withOwner($user)->create([
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->endOfMonth(),
        ]);
    }

    return Livewire::test(MonthCalendar::class, ['travel' => $travel]);
}

test('month calendar component can be rendered', function () {
    createMonthCalendarComponent()->assertStatus(200);
});

test('component initializes with travel data', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $startDate = Carbon::create(2025, 6, 1);
    $endDate = Carbon::create(2025, 6, 30);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    expect($component->get('travel')->id)->toBe($travel->id);
    expect($component->get('currentYear'))->toBe(2025);
    expect($component->get('currentMonth'))->toBe(6);
    expect($component->get('monthName'))->toBe('Juin'); // French locale
});

test('component initializes to current month when today is within travel dates', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $today = Carbon::today();
    $startDate = $today->copy()->subDays(10);
    $endDate = $today->copy()->addDays(10);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    expect($component->get('currentYear'))->toBe($today->year);
    expect($component->get('currentMonth'))->toBe($today->month);
});

test('component initializes to travel start date when today is outside travel dates', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travelStart = Carbon::create(2025, 8, 1);
    $travelEnd = Carbon::create(2025, 8, 31);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => $travelStart,
        'end_date' => $travelEnd,
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    expect($component->get('currentYear'))->toBe(2025);
    expect($component->get('currentMonth'))->toBe(8);
});

test('calendar generates correct structure', function () {
    $component = createMonthCalendarComponent();

    $days = $component->get('days');

    // Should have weeks of 7 days each
    expect($days)->toBeArray();
    foreach ($days as $week) {
        expect($week)->toHaveCount(7);
        foreach ($week as $day) {
            expect($day)->toHaveKeys(['day', 'month', 'year', 'isToday', 'events']);
        }
    }
});

test('calendar includes events from travel activities', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->endOfMonth(),
    ]);

    $activity = Activity::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Test Activity',
        'start_date' => Carbon::now()->addDays(5),
        'end_date' => Carbon::now()->addDays(5),
        'start_time' => '10:00',
        'end_time' => '12:00',
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $days = $component->get('days');
    $hasActivityEvent = false;

    foreach ($days as $week) {
        foreach ($week as $day) {
            if (! empty($day['events'])) {
                foreach ($day['events'] as $event) {
                    if ($event['name'] === 'Test Activity' && $event['type'] === 'activity') {
                        $hasActivityEvent = true;
                        expect($event['start_time'])->toBe('10:00');
                        expect($event['end_time'])->toBe('12:00');
                        break 3;
                    }
                }
            }
        }
    }

    expect($hasActivityEvent)->toBeTrue();
});

test('calendar includes events from travel housings', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->endOfMonth(),
    ]);

    $housing = Housing::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Test Hotel',
        'start_date' => Carbon::now()->addDays(3),
        'end_date' => Carbon::now()->addDays(6),
        'start_time' => '15:00',
        'end_time' => '11:00',
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $days = $component->get('days');
    $hasHousingEvent = false;

    foreach ($days as $week) {
        foreach ($week as $day) {
            if (! empty($day['events'])) {
                foreach ($day['events'] as $event) {
                    if ($event['name'] === 'Test Hotel' && $event['type'] === 'housing') {
                        $hasHousingEvent = true;
                        expect($event['start_time'])->toBe('15:00');
                        expect($event['end_time'])->toBe('11:00');
                        break 3;
                    }
                }
            }
        }
    }

    expect($hasHousingEvent)->toBeTrue();
});

test('can navigate to next month', function () {
    $component = createMonthCalendarComponent();

    $currentMonth = $component->get('currentMonth');
    $currentYear = $component->get('currentYear');

    $component->call('next');

    $expectedDate = Carbon::create($currentYear, $currentMonth, 1)->addMonth();

    expect($component->get('currentMonth'))->toBe($expectedDate->month);
    expect($component->get('currentYear'))->toBe($expectedDate->year);
});

test('can navigate to previous month', function () {
    $component = createMonthCalendarComponent();

    $currentMonth = $component->get('currentMonth');
    $currentYear = $component->get('currentYear');

    $component->call('previous');

    $expectedDate = Carbon::create($currentYear, $currentMonth, 1)->subMonth();

    expect($component->get('currentMonth'))->toBe($expectedDate->month);
    expect($component->get('currentYear'))->toBe($expectedDate->year);
});

test('housing bars are generated correctly', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->endOfMonth(),
    ]);

    // Create a housing that spans several days
    $housing = Housing::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Test Hotel',
        'place_name' => 'Paris',
        'place_latitude' => 48.8566,
        'place_longitude' => 2.3522,
        'start_date' => Carbon::now()->startOfMonth()->addDays(5), // Monday
        'end_date' => Carbon::now()->startOfMonth()->addDays(8),   // Thursday
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $housingBars = $component->get('housingBars');

    expect($housingBars)->toBeArray();

    // Should have housing bars for the week containing the housing
    $hasHousingBar = false;
    foreach ($housingBars as $weekIndex => $weekBars) {
        if (! empty($weekBars)) {
            foreach ($weekBars as $level => $bars) {
                foreach ($bars as $bar) {
                    if ($bar['name'] === 'Test Hotel') {
                        expect($bar['place'])->toBe('Paris');
                        expect($bar['latitude'])->toBe('48.8566');
                        expect($bar['longitude'])->toBe('2.3522');
                        expect($bar['colStart'])->toBeGreaterThanOrEqual(1);
                        expect($bar['colStart'])->toBeLessThanOrEqual(7);
                        expect($bar['span'])->toBeGreaterThanOrEqual(1);
                        $hasHousingBar = true;
                        break 3;
                    }
                }
            }
        }
    }

    expect($hasHousingBar)->toBeTrue();
});

test('housing bars stack correctly when overlapping', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->endOfMonth(),
    ]);

    // Create two overlapping housings in the same week
    $startDate = Carbon::now()->startOfMonth()->addDays(10); // Ensure they're in the same week

    $housing1 = Housing::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Hotel A',
        'start_date' => $startDate,
        'end_date' => $startDate->copy()->addDays(2),
    ]);

    $housing2 = Housing::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Hotel B',
        'start_date' => $startDate->copy()->addDays(1), // Overlaps with Hotel A
        'end_date' => $startDate->copy()->addDays(3),
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $housingBars = $component->get('housingBars');

    // Find the week with both housings
    $foundOverlappingBars = false;
    foreach ($housingBars as $weekIndex => $weekBars) {
        if (count($weekBars) > 1) { // Multiple levels indicates stacking
            $foundOverlappingBars = true;
            break;
        }
    }

    expect($foundOverlappingBars)->toBeTrue();
});

test('housing bars handle non-overlapping housings on same level', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->endOfMonth(),
    ]);

    // Create two non-overlapping housings in the same week
    $startDate = Carbon::now()->startOfMonth()->addDays(10);

    $housing1 = Housing::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Hotel A',
        'start_date' => $startDate,
        'end_date' => $startDate->copy()->addDays(1),
    ]);

    $housing2 = Housing::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Hotel B',
        'start_date' => $startDate->copy()->addDays(3), // No overlap
        'end_date' => $startDate->copy()->addDays(4),
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $housingBars = $component->get('housingBars');

    // Find the week with both housings - they should be on the same level
    $foundSameLevelBars = false;
    foreach ($housingBars as $weekIndex => $weekBars) {
        foreach ($weekBars as $level => $bars) {
            if (count($bars) >= 2) {
                $foundSameLevelBars = true;
                break 2;
            }
        }
    }

    expect($foundSameLevelBars)->toBeTrue();
});

test('today is marked correctly in calendar', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $today = Carbon::today();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => $today->copy()->subDays(5),
        'end_date' => $today->copy()->addDays(5),
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $days = $component->get('days');
    $todayFound = false;

    foreach ($days as $week) {
        foreach ($week as $day) {
            if ($day['year'] == $today->year &&
                $day['month'] == $today->month &&
                $day['day'] == $today->day) {
                expect($day['isToday'])->toBeTrue();
                $todayFound = true;
            }
        }
    }

    expect($todayFound)->toBeTrue();
});

test('component responds to activityCreated event', function () {
    $component = createMonthCalendarComponent();

    // Dispatch the event
    $component->dispatch('activityCreated');

    // Component should update calendar and remain functional
    expect($component->instance())->toBeInstanceOf(MonthCalendar::class);
    expect($component->get('days'))->toBeArray();
});

test('component responds to housingCreated event', function () {
    $component = createMonthCalendarComponent();

    // Dispatch the event
    $component->dispatch('housingCreated');

    // Component should update calendar and remain functional
    expect($component->instance())->toBeInstanceOf(MonthCalendar::class);
    expect($component->get('days'))->toBeArray();
});

test('navigation handles year transitions correctly', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::create(2024, 12, 1),
        'end_date' => Carbon::create(2024, 12, 31),
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    // Should initialize to December 2024
    expect($component->get('currentMonth'))->toBe(12);
    expect($component->get('currentYear'))->toBe(2024);

    // Navigate to next month (January 2025)
    $component->call('next');

    expect($component->get('currentMonth'))->toBe(1);
    expect($component->get('currentYear'))->toBe(2025);

    // Navigate back to December 2024
    $component->call('previous');

    expect($component->get('currentMonth'))->toBe(12);
    expect($component->get('currentYear'))->toBe(2024);
});

test('housing bars handle housings that span multiple weeks', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->endOfMonth(),
    ]);

    // Create a housing that spans from one week to the next
    $startDate = Carbon::now()->startOfMonth()->addDays(5); // Should be in first week
    $endDate = $startDate->copy()->addDays(10); // Should extend into next week

    $housing = Housing::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Long Stay Hotel',
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $housingBars = $component->get('housingBars');

    // Should have housing bars in multiple weeks
    $weeksWithBars = 0;
    foreach ($housingBars as $weekIndex => $weekBars) {
        if (! empty($weekBars)) {
            $weeksWithBars++;
        }
    }

    expect($weeksWithBars)->toBeGreaterThan(1);
});

test('navigation methods handle invalid carbon date creation gracefully', function () {
    $component = createMonthCalendarComponent();

    // Set invalid state and try navigation
    $component->set('currentYear', 0);
    $component->set('currentMonth', 0);

    // These should not crash
    $component->call('next');
    $component->call('previous');

    // Component should still be functional
    expect($component->instance())->toBeInstanceOf(MonthCalendar::class);
});

test('housing bars handle empty housing collections', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->endOfMonth(),
    ]);

    // No housings created for this travel

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $housingBars = $component->get('housingBars');

    // Should handle empty housing collection gracefully
    expect($housingBars)->toBeArray();
    // All weeks should have empty or no housing bars
    foreach ($housingBars as $weekBars) {
        expect($weekBars)->toBeArray();
    }
});

test('getDayEvents integration works correctly', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->endOfMonth(),
    ]);

    $eventDate = Carbon::now()->addDays(5);

    // Create both activity and housing on the same day
    $activity = Activity::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Morning Activity',
        'start_date' => $eventDate,
        'end_date' => $eventDate,
        'start_time' => '09:00',
    ]);

    $housing = Housing::factory()->create([
        'travel_id' => $travel->id,
        'name' => 'Evening Hotel',
        'start_date' => $eventDate,
        'end_date' => $eventDate,
        'start_time' => '18:00',
    ]);

    $component = Livewire::test(MonthCalendar::class, ['travel' => $travel]);

    $days = $component->get('days');
    $foundBothEvents = false;

    foreach ($days as $week) {
        foreach ($week as $day) {
            if ($day['day'] == $eventDate->day &&
                $day['month'] == $eventDate->month &&
                $day['year'] == $eventDate->year) {

                $events = $day['events'];
                $activityFound = false;
                $housingFound = false;

                foreach ($events as $event) {
                    if ($event['name'] === 'Morning Activity' && $event['type'] === 'activity') {
                        $activityFound = true;
                    }
                    if ($event['name'] === 'Evening Hotel' && $event['type'] === 'housing') {
                        $housingFound = true;
                    }
                }

                if ($activityFound && $housingFound) {
                    $foundBothEvents = true;
                    // Events should be sorted by start time (09:00 before 18:00)
                    expect($events[0]['start_time'])->toBe('09:00');
                }
                break 2;
            }
        }
    }

    expect($foundBothEvents)->toBeTrue();
});
