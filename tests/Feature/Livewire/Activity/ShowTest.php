<?php

use App\Livewire\Activity\Show;
use App\Models\Activity;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('activity show component can be rendered', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'activityId' => $activity->id,
    ])->assertStatus(200);
});

test('component initializes with travel and activity data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Test Travel',
    ]);
    $activity = Activity::factory()->forTravel($travel)->create([
        'name' => 'Test Activity',
        'description' => 'Test Description',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'activityId' => $activity->id,
    ]);

    expect($component->get('travel')->id)->toBe($travel->id);
    expect($component->get('activity')->id)->toBe($activity->id);
    expect($component->get('activity')->name)->toBe('Test Activity');
    expect($component->get('medias'))->toBeCollection();
});

test('component loads media display for activity', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $activity = Activity::factory()->forTravel($travel)->create();

    // Add media to the activity
    $file = UploadedFile::fake()->image('test.jpg');
    $activity->addMedia($file->getRealPath())
        ->usingName('test-image.jpg')
        ->toMediaCollection();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'activityId' => $activity->id,
    ]);

    $medias = $component->get('medias');
    expect($medias)->toHaveCount(1);
    expect($medias[0])->toHaveKeys(['id', 'url', 'name']);
    expect($medias[0]['name'])->toBeString(); // File name will be auto-generated
});

test('refresh activity updates activity and media data', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $activity = Activity::factory()->forTravel($travel)->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'activityId' => $activity->id,
    ]);

    // Update the activity in the database
    $activity->update(['name' => 'Updated Name']);

    // Add media to the activity
    $file = UploadedFile::fake()->image('new-test.jpg');
    $activity->addMedia($file->getRealPath())
        ->usingName('new-image.jpg')
        ->toMediaCollection();

    // Trigger refresh
    $component->call('refreshActivity')
        ->assertDispatched('activity-refreshed')
        ->assertDispatched('media-refreshed');

    // Check that the activity data is refreshed
    expect($component->get('activity')->name)->toBe('Updated Name');
    expect($component->get('medias'))->toHaveCount(1);
    expect($component->get('medias')[0]['name'])->toBeString(); // File name will be auto-generated
});

test('throws exception when travel not found', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(Show::class, [
        'travelId' => '01988e63-0000-7000-8000-000000000000', // Valid UUID format
        'activityId' => '01988e63-0000-7000-8000-000000000001',
    ]);
});

test('throws exception when activity not found', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'activityId' => '01988e63-0000-7000-8000-000000000001', // Valid UUID format
    ]);
});

test('throws exception when activity does not belong to travel', function () {
    $user = User::factory()->create();
    $travel1 = Travel::factory()->withOwner($user)->create();
    $travel2 = Travel::factory()->withOwner($user)->create();
    $activity = Activity::factory()->forTravel($travel2)->create();

    $this->actingAs($user);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    // Trying to access activity from travel2 using travel1's ID
    Livewire::test(Show::class, [
        'travelId' => $travel1->id,
        'activityId' => $activity->id,
    ]);
});

test('user must be travel member to view activity', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create();
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($otherUser);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    // Non-member cannot access the travel due to global scope
    Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'activityId' => $activity->id,
    ]);
});

test('handles activity with no media', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'activityId' => $activity->id,
    ]);

    expect($component->get('medias'))->toBeCollection();
    expect($component->get('medias'))->toBeEmpty();
});

test('refresh activity works when no new media added', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $activity = Activity::factory()->forTravel($travel)->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'activityId' => $activity->id,
    ]);

    // Update only the activity name
    $activity->update(['name' => 'Updated Name']);

    // Trigger refresh
    $component->call('refreshActivity')
        ->assertDispatched('activity-refreshed')
        ->assertDispatched('media-refreshed');

    // Check that the activity data is refreshed
    expect($component->get('activity')->name)->toBe('Updated Name');
    expect($component->get('medias'))->toBeEmpty();
});
