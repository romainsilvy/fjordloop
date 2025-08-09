<?php

use App\Livewire\Travel\GlobalMap;
use App\Models\Activity;
use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use Livewire\Livewire;

test('global map component can be rendered', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    Livewire::test(GlobalMap::class, ['travel' => $travel])
        ->assertStatus(200);
});

test('component initializes with travel data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Test Travel',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    expect($component->get('travel')->id)->toBe($travel->id);
    expect($component->get('travel')->name)->toBe('Test Travel');
});

test('component loads activities with place information', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Create activity with place
    $activityWithPlace = Activity::factory()->forTravel($travel)->create([
        'name' => 'Activity with Place',
        'place_name' => 'Paris Museum',
        'place_latitude' => 48.8566,
        'place_longitude' => 2.3522,
    ]);

    // Create activity without place
    Activity::factory()->forTravel($travel)->create([
        'name' => 'Activity without Place',
        'place_name' => null,
        'place_latitude' => null,
        'place_longitude' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    $activities = $component->get('activities');
    expect($activities)->toHaveCount(1);
    expect($activities->first()->name)->toBe('Activity with Place');
});

test('component loads housings with place information', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Create housing with place
    $housingWithPlace = Housing::factory()->forTravel($travel)->create([
        'name' => 'Housing with Place',
        'place_name' => 'Paris Hotel',
        'place_latitude' => 48.8566,
        'place_longitude' => 2.3522,
    ]);

    // Create housing without place
    Housing::factory()->forTravel($travel)->create([
        'name' => 'Housing without Place',
        'place_name' => null,
        'place_latitude' => null,
        'place_longitude' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    $housings = $component->get('housings');
    expect($housings)->toHaveCount(1);
    expect($housings->first()->name)->toBe('Housing with Place');
});

test('component handles travel with no activities', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    expect($component->get('activities'))->toBeEmpty();
});

test('component handles travel with no housings', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    expect($component->get('housings'))->toBeEmpty();
});

test('component handles travel with no activities or housings with place', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Create activities and housings without place information
    Activity::factory()->forTravel($travel)->create([
        'place_name' => null,
        'place_latitude' => null,
        'place_longitude' => null,
    ]);

    Housing::factory()->forTravel($travel)->create([
        'place_name' => null,
        'place_latitude' => null,
        'place_longitude' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    expect($component->get('activities'))->toBeEmpty();
    expect($component->get('housings'))->toBeEmpty();
});

test('component loads multiple activities and housings with places', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Create multiple activities with places
    Activity::factory()->forTravel($travel)->create([
        'name' => 'Activity 1',
        'place_name' => 'Museum',
        'place_latitude' => 48.8566,
        'place_longitude' => 2.3522,
    ]);

    Activity::factory()->forTravel($travel)->create([
        'name' => 'Activity 2',
        'place_name' => 'Park',
        'place_latitude' => 48.8606,
        'place_longitude' => 2.3376,
    ]);

    // Create multiple housings with places
    Housing::factory()->forTravel($travel)->create([
        'name' => 'Hotel 1',
        'place_name' => 'Downtown Hotel',
        'place_latitude' => 48.8584,
        'place_longitude' => 2.2945,
    ]);

    Housing::factory()->forTravel($travel)->create([
        'name' => 'Hotel 2',
        'place_name' => 'Airport Hotel',
        'place_latitude' => 49.0097,
        'place_longitude' => 2.5479,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    expect($component->get('activities'))->toHaveCount(2);
    expect($component->get('housings'))->toHaveCount(2);
});

// Note: Testing non-member access is complex because the Travel global scope
// would prevent the travel from being found in the first place, making this component
// inaccessible to non-members through normal routing. The authorization is handled
// at the model level rather than the component level.

test('component respects hasPlace scope for activities', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Activity with complete place information
    Activity::factory()->forTravel($travel)->create([
        'name' => 'Complete Activity',
        'place_name' => 'Complete Place',
        'place_latitude' => 48.8566,
        'place_longitude' => 2.3522,
    ]);

    // Activity with missing place_name
    Activity::factory()->forTravel($travel)->create([
        'name' => 'Incomplete Activity 1',
        'place_name' => null,
        'place_latitude' => 48.8566,
        'place_longitude' => 2.3522,
    ]);

    // Activity with missing coordinates
    Activity::factory()->forTravel($travel)->create([
        'name' => 'Incomplete Activity 2',
        'place_name' => 'Some Place',
        'place_latitude' => null,
        'place_longitude' => 2.3522,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    // Only the activity with complete place information should be loaded
    expect($component->get('activities'))->toHaveCount(1);
    expect($component->get('activities')->first()->name)->toBe('Complete Activity');
});

test('component respects hasPlace scope for housings', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Housing with complete place information
    Housing::factory()->forTravel($travel)->create([
        'name' => 'Complete Housing',
        'place_name' => 'Complete Place',
        'place_latitude' => 48.8566,
        'place_longitude' => 2.3522,
    ]);

    // Housing with missing place_name
    Housing::factory()->forTravel($travel)->create([
        'name' => 'Incomplete Housing 1',
        'place_name' => null,
        'place_latitude' => 48.8566,
        'place_longitude' => 2.3522,
    ]);

    // Housing with missing coordinates
    Housing::factory()->forTravel($travel)->create([
        'name' => 'Incomplete Housing 2',
        'place_name' => 'Some Place',
        'place_latitude' => null,
        'place_longitude' => 2.3522,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(GlobalMap::class, ['travel' => $travel]);

    // Only the housing with complete place information should be loaded
    expect($component->get('housings'))->toHaveCount(1);
    expect($component->get('housings')->first()->name)->toBe('Complete Housing');
});
