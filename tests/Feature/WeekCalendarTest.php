<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Travel;
use Livewire\Livewire;

use App\Models\Activity;
use App\Livewire\WeekCalendar;
use App\Models\Housing;

use function Pest\Laravel\{get};

beforeEach(function () {
    // Freeze time for consistent testing
    Carbon::setTestNow('2024-03-20 12:00:00');
});

it('mounts correctly with current week', function () {
    // Create a travel instance
    $travel = Travel::factory()->create([
        'start_date' => '2024-03-01',
        'end_date' => '2024-03-31'
    ]);

    // Test the component
    Livewire::test(WeekCalendar::class, ['travel' => $travel])
        ->assertSet('currentYear', 2024)
        ->assertSet('currentMonth', 3)
        ->assertSet('currentWeek', Carbon::now()->weekOfYear)
        ->assertSet('startDateString', '18 mars 2024')
        ->assertSet('endDateString', '24 mars 2024')
        ->assertCount('days', 7);
});

it('navigates to previous week correctly', function () {
    $travel = Travel::factory()->create([
        'start_date' => '2024-03-01',
        'end_date' => '2024-03-31'
    ]);

    Livewire::test(WeekCalendar::class, ['travel' => $travel])
        ->call('previous')
        ->assertSet('startDateString', '11 mars 2024')
        ->assertSet('endDateString', '17 mars 2024')
        ->assertCount('days', 7);
});

it('navigates to next week correctly', function () {
    $travel = Travel::factory()->create([
        'start_date' => '2024-03-01',
        'end_date' => '2024-03-31'
    ]);

    Livewire::test(WeekCalendar::class, ['travel' => $travel])
        ->call('next')
        ->assertSet('startDateString', '25 mars 2024')
        ->assertSet('endDateString', '31 mars 2024')
        ->assertCount('days', 7);
});

it('updates calendar when activity is created', function () {
    $owner = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create([
        'start_date' => Carbon::now()->startOfWeek(Carbon::MONDAY),
        'end_date' => Carbon::now()->endOfWeek(Carbon::MONDAY)
    ]);
    $this->actingAs($owner);

    $component = Livewire::test(WeekCalendar::class, ['travel' => $travel]);

    // Initial state
    $initialDays = $component->get('days');

    // Create an activity
    Activity::factory(3)->create(['travel_id' => $travel->id, 'start_date' => Carbon::now()->startOfWeek(Carbon::MONDAY), 'end_date' => Carbon::now()->startOfWeek(Carbon::MONDAY)->addDay()]);

    // Simulate activity creation event and wait for it to be processed
    $component->dispatch('activityCreated');


    expect($component->get('days'))->not->toBe($initialDays)
        ->and($component->get('days'))->toHaveCount(7);
});

it('updates calendar when housing is created', function () {
    $owner = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create([
        'start_date' => Carbon::now()->startOfWeek(Carbon::MONDAY),
        'end_date' => Carbon::now()->endOfWeek(Carbon::MONDAY)
    ]);
    $this->actingAs($owner);

    $component = Livewire::test(WeekCalendar::class, ['travel' => $travel]);

    // Initial state
    $initialDays = $component->get('days');

    // Create an housing
    Housing::factory(3)->create(['travel_id' => $travel->id, 'start_date' => Carbon::now()->startOfWeek(Carbon::MONDAY), 'end_date' => Carbon::now()->startOfWeek(Carbon::MONDAY)->addDay()]);

    // Simulate housing creation event and wait for it to be processed
    $component->dispatch('housingCreated');


    expect($component->get('days'))->not->toBe($initialDays)
        ->and($component->get('days'))->toHaveCount(7);
});

it('displays correct day information', function () {
    $travel = Travel::factory()->create([
        'start_date' => '2024-03-01',
        'end_date' => '2024-03-31'
    ]);

    $component = Livewire::test(WeekCalendar::class, ['travel' => $travel]);

    $days = $component->get('days');

    // Test structure of first day
    $firstDay = $days[0];
    expect($firstDay)->toHaveKeys([
        'day',
        'month',
        'year',
        'dayName',
        'shortDayName',
        'date',
        'isToday',
        'events'
    ]);

    // Test if today is marked correctly
    $todayKey = array_search(true, array_column($days, 'isToday'));
    expect($todayKey)->not->toBeNull();
    expect($days[$todayKey]['date'])->toBe('2024-03-20');
});

it('formats dates correctly', function () {
    $travel = Travel::factory()->create([
        'start_date' => '2024-03-01',
        'end_date' => '2024-03-31'
    ]);

    $component = Livewire::test(WeekCalendar::class, ['travel' => $travel]);

    // Test date formatting with French month names
    expect($component->get('startDateString'))->toMatch('/^\d{2} [a-zéû]{3,} \d{4}$/');
    expect($component->get('endDateString'))->toMatch('/^\d{2} [a-zéû]{3,} \d{4}$/');
});
