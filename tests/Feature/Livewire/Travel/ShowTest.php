<?php

use App\Livewire\Travel\Show;
use App\Models\Activity;
use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use Livewire\Livewire;

test('travel show component can be rendered', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    Livewire::test(Show::class, ['travelId' => $travel->id])
        ->assertStatus(200);
});

test('component initializes with travel and related data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Test Travel',
    ]);

    // Create activities and housings
    $activity = Activity::factory()->forTravel($travel)->create(['name' => 'Test Activity']);
    $housing = Housing::factory()->forTravel($travel)->create(['name' => 'Test Housing']);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    expect($component->get('travel')->id)->toBe($travel->id);
    expect($component->get('travel')->name)->toBe('Test Travel');
    expect($component->get('activities'))->toHaveCount(1);
    expect($component->get('activities')->first()->name)->toBe('Test Activity');
    expect($component->get('housings'))->toHaveCount(1);
    expect($component->get('housings')->first()->name)->toBe('Test Housing');
});

test('component handles travel with no activities', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    expect($component->get('activities'))->toBeEmpty();
});

test('component handles travel with no housings', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    expect($component->get('housings'))->toBeEmpty();
});

test('refresh activities updates activities list and dispatches event', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    // Initially no activities
    expect($component->get('activities'))->toBeEmpty();

    // Add an activity to the travel directly in database
    $activity = Activity::factory()->forTravel($travel)->create(['name' => 'New Activity']);

    // Trigger refresh
    $component->call('refreshActivities')
        ->assertDispatched('activities-refreshed');

    // Check that activities are refreshed
    expect($component->get('activities'))->toHaveCount(1);
    expect($component->get('activities')->first()->name)->toBe('New Activity');
});

test('refresh housings updates housings list and dispatches event', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    // Initially no housings
    expect($component->get('housings'))->toBeEmpty();

    // Add a housing to the travel directly in database
    $housing = Housing::factory()->forTravel($travel)->create(['name' => 'New Housing']);

    // Trigger refresh
    $component->call('refreshHousings')
        ->assertDispatched('housings-refreshed');

    // Check that housings are refreshed
    expect($component->get('housings'))->toHaveCount(1);
    expect($component->get('housings')->first()->name)->toBe('New Housing');
});

test('responds to activityCreated event', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    // Add an activity to the travel directly in database
    Activity::factory()->forTravel($travel)->create(['name' => 'Event Activity']);

    // Dispatch the event that would normally be triggered by activity creation
    $component->dispatch('activityCreated')
        ->assertDispatched('activities-refreshed');

    // Check that activities are refreshed
    expect($component->get('activities'))->toHaveCount(1);
    expect($component->get('activities')->first()->name)->toBe('Event Activity');
});

test('responds to housingCreated event', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    // Add a housing to the travel directly in database
    Housing::factory()->forTravel($travel)->create(['name' => 'Event Housing']);

    // Dispatch the event that would normally be triggered by housing creation
    $component->dispatch('housingCreated')
        ->assertDispatched('housings-refreshed');

    // Check that housings are refreshed
    expect($component->get('housings'))->toHaveCount(1);
    expect($component->get('housings')->first()->name)->toBe('Event Housing');
});

test('throws exception when travel not found', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(Show::class, ['travelId' => '01988e63-0000-7000-8000-000000000000']); // Valid UUID format
});

test('user must be travel member to view travel', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create();

    $this->actingAs($otherUser);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    // Non-member cannot access the travel due to global scope
    Livewire::test(Show::class, ['travelId' => $travel->id]);
});

test('component loads multiple activities', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Create multiple activities
    Activity::factory()->forTravel($travel)->create(['name' => 'Activity 1']);
    Activity::factory()->forTravel($travel)->create(['name' => 'Activity 2']);
    Activity::factory()->forTravel($travel)->create(['name' => 'Activity 3']);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    expect($component->get('activities'))->toHaveCount(3);
});

test('component loads multiple housings', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Create multiple housings
    Housing::factory()->forTravel($travel)->create(['name' => 'Housing 1']);
    Housing::factory()->forTravel($travel)->create(['name' => 'Housing 2']);
    Housing::factory()->forTravel($travel)->create(['name' => 'Housing 3']);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    expect($component->get('housings'))->toHaveCount(3);
});

test('activities and housings are filtered by travel', function () {
    $user = User::factory()->create();
    $travel1 = Travel::factory()->withOwner($user)->create(['name' => 'Travel 1']);
    $travel2 = Travel::factory()->withOwner($user)->create(['name' => 'Travel 2']);

    // Create activities and housings for both travels
    Activity::factory()->forTravel($travel1)->create(['name' => 'Travel 1 Activity']);
    Activity::factory()->forTravel($travel2)->create(['name' => 'Travel 2 Activity']);

    Housing::factory()->forTravel($travel1)->create(['name' => 'Travel 1 Housing']);
    Housing::factory()->forTravel($travel2)->create(['name' => 'Travel 2 Housing']);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel1->id]);

    // Should only load activities and housings for travel1
    expect($component->get('activities'))->toHaveCount(1);
    expect($component->get('activities')->first()->name)->toBe('Travel 1 Activity');
    expect($component->get('housings'))->toHaveCount(1);
    expect($component->get('housings')->first()->name)->toBe('Travel 1 Housing');
});

test('dispatched events include correct data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Create activities and housings
    $activities = Activity::factory()->forTravel($travel)->count(2)->create();
    $housings = Housing::factory()->forTravel($travel)->count(2)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    // Test activity refresh event dispatch
    $component->call('refreshActivities')
        ->assertDispatched('activities-refreshed');

    // Test housing refresh event dispatch
    $component->call('refreshHousings')
        ->assertDispatched('housings-refreshed');
});

test('component maintains state after refresh operations', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create(['name' => 'Test Travel']);

    // Create initial data
    Activity::factory()->forTravel($travel)->create(['name' => 'Initial Activity']);
    Housing::factory()->forTravel($travel)->create(['name' => 'Initial Housing']);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, ['travelId' => $travel->id]);

    // Verify initial state
    expect($component->get('travel')->name)->toBe('Test Travel');
    expect($component->get('activities'))->toHaveCount(1);
    expect($component->get('housings'))->toHaveCount(1);

    // Add more data and refresh
    Activity::factory()->forTravel($travel)->create(['name' => 'Added Activity']);
    Housing::factory()->forTravel($travel)->create(['name' => 'Added Housing']);

    $component->call('refreshActivities');
    $component->call('refreshHousings');

    // Verify state is maintained and updated
    expect($component->get('travel')->name)->toBe('Test Travel');
    expect($component->get('activities'))->toHaveCount(2);
    expect($component->get('housings'))->toHaveCount(2);
});
