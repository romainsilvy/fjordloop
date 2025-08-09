<?php

use App\Livewire\Travel\Index;
use App\Models\Travel;
use App\Models\User;
use Livewire\Livewire;

test('travel index component can be rendered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertStatus(200);
});

test('component initializes with empty sections when no travels', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    expect($component->get('sections'))->toBeEmpty();
});

test('component categorizes active travels', function () {
    $user = User::factory()->create();

    // Create active travel (ongoing now)
    Travel::factory()->withOwner($user)->create([
        'name' => 'Active Travel',
        'start_date' => now()->subDays(2),
        'end_date' => now()->addDays(2),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(1);
    expect($sections[0]['title'])->toBe('Actifs');
    expect($sections[0]['travels'])->toHaveCount(1);
    expect($sections[0]['travels']->first()->name)->toBe('Active Travel');
});

test('component categorizes upcoming travels', function () {
    $user = User::factory()->create();

    // Create upcoming travel
    Travel::factory()->withOwner($user)->create([
        'name' => 'Upcoming Travel',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(1);
    expect($sections[0]['title'])->toBe('À venir');
    expect($sections[0]['travels'])->toHaveCount(1);
    expect($sections[0]['travels']->first()->name)->toBe('Upcoming Travel');
});

test('component categorizes past travels', function () {
    $user = User::factory()->create();

    // Create past travel
    Travel::factory()->withOwner($user)->create([
        'name' => 'Past Travel',
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(5),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(1);
    expect($sections[0]['title'])->toBe('Passés');
    expect($sections[0]['travels'])->toHaveCount(1);
    expect($sections[0]['travels']->first()->name)->toBe('Past Travel');
});

test('component categorizes travels with no dates', function () {
    $user = User::factory()->create();

    // Create travel with no dates
    Travel::factory()->withOwner($user)->create([
        'name' => 'No Date Travel',
        'start_date' => null,
        'end_date' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(1);
    expect($sections[0]['title'])->toBe('Pas de date renseignée');
    expect($sections[0]['travels'])->toHaveCount(1);
    expect($sections[0]['travels']->first()->name)->toBe('No Date Travel');
});

test('component categorizes multiple travels in correct sections', function () {
    $user = User::factory()->create();

    // Create travels in all categories
    Travel::factory()->withOwner($user)->create([
        'name' => 'Active Travel',
        'start_date' => now()->subDays(1),
        'end_date' => now()->addDays(1),
    ]);

    Travel::factory()->withOwner($user)->create([
        'name' => 'Upcoming Travel',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
    ]);

    Travel::factory()->withOwner($user)->create([
        'name' => 'Past Travel',
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(5),
    ]);

    Travel::factory()->withOwner($user)->create([
        'name' => 'No Date Travel',
        'start_date' => null,
        'end_date' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(4);

    // Check section order (active first, then upcoming, past, no date)
    expect($sections[0]['title'])->toBe('Actifs');
    expect($sections[1]['title'])->toBe('À venir');
    expect($sections[2]['title'])->toBe('Passés');
    expect($sections[3]['title'])->toBe('Pas de date renseignée');
});

test('component handles multiple travels in same category', function () {
    $user = User::factory()->create();

    // Create multiple upcoming travels
    Travel::factory()->withOwner($user)->create([
        'name' => 'Upcoming Travel 1',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
    ]);

    Travel::factory()->withOwner($user)->create([
        'name' => 'Upcoming Travel 2',
        'start_date' => now()->addDays(15),
        'end_date' => now()->addDays(20),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(1);
    expect($sections[0]['title'])->toBe('À venir');
    expect($sections[0]['travels'])->toHaveCount(2);
});

test('component excludes empty sections', function () {
    $user = User::factory()->create();

    // Create only upcoming travel
    Travel::factory()->withOwner($user)->create([
        'name' => 'Upcoming Travel',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(1); // Only upcoming section
    expect($sections[0]['title'])->toBe('À venir');
});

test('component respects travel global scope', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create travel for user1
    Travel::factory()->withOwner($user1)->create([
        'name' => 'User1 Travel',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
    ]);

    // Create travel for user2
    Travel::factory()->withOwner($user2)->create([
        'name' => 'User2 Travel',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
    ]);

    $this->actingAs($user1);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(1);
    expect($sections[0]['travels'])->toHaveCount(1);
    expect($sections[0]['travels']->first()->name)->toBe('User1 Travel');
});

test('component handles travel at exact current time boundaries', function () {
    $user = User::factory()->create();

    // Travel starting exactly now
    Travel::factory()->withOwner($user)->create([
        'name' => 'Starting Now Travel',
        'start_date' => now(),
        'end_date' => now()->addDays(5),
    ]);

    // Travel ending exactly now
    Travel::factory()->withOwner($user)->create([
        'name' => 'Ending Now Travel',
        'start_date' => now()->subDays(5),
        'end_date' => now(),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(1);
    expect($sections[0]['title'])->toBe('Actifs');
    expect($sections[0]['travels'])->toHaveCount(2);
});

test('component handles travels with only start date', function () {
    $user = User::factory()->create();

    // Travel with only start date in future
    Travel::factory()->withOwner($user)->create([
        'name' => 'Start Only Future',
        'start_date' => now()->addDays(5),
        'end_date' => null,
    ]);

    // Travel with only start date in past
    Travel::factory()->withOwner($user)->create([
        'name' => 'Start Only Past',
        'start_date' => now()->subDays(5),
        'end_date' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    // These should be categorized based on their start dates
    expect($sections)->toHaveCount(2);
});

test('component handles travels with only end date', function () {
    $user = User::factory()->create();

    // Travel with only end date in future
    Travel::factory()->withOwner($user)->create([
        'name' => 'End Only Future',
        'start_date' => null,
        'end_date' => now()->addDays(5),
    ]);

    // Travel with only end date in past
    Travel::factory()->withOwner($user)->create([
        'name' => 'End Only Past',
        'start_date' => null,
        'end_date' => now()->subDays(5),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);

    $sections = $component->get('sections');
    expect($sections)->toHaveCount(2);
});

test('unauthenticated user sees no travels', function () {
    // Don't authenticate user

    $component = Livewire::test(Index::class);

    expect($component->get('sections'))->toBeEmpty();
});
