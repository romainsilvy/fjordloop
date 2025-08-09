<?php

use App\Livewire\Activity\Update;
use App\Models\Activity;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Masmerise\Toaster\Toaster;

test('activity update component can be rendered', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['activity' => $activity])
        ->assertStatus(200);
});

test('component initializes with activity data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create([
        'name' => 'Test Activity',
        'description' => 'Test Description',
        'url' => 'https://example.com',
        'place_name' => 'Paris, France',
        'place_latitude' => '48.8566',
        'place_longitude' => '2.3522',
        'price_by_person' => '25.50',
        'start_date' => now()->addDays(2),
        'start_time' => '09:00',
        'end_date' => now()->addDays(2),
        'end_time' => '17:00',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['activity' => $activity]);

    expect($component->get('activity')->id)->toBe($activity->id);
    expect($component->get('name'))->toBe('Test Activity');
    expect($component->get('description'))->toBe('Test Description');
    expect($component->get('url'))->toBe('https://example.com');
    expect($component->get('place')['display_name'])->toBe('Paris, France');
    expect($component->get('place')['lat'])->toBe('48.8566');
    expect($component->get('place')['lng'])->toBe('2.3522');
    expect($component->get('price'))->toBe('25.50');
    expect($component->get('priceType'))->toBe('price_by_person');
    expect($component->get('startDate'))->toBe(now()->addDays(2)->format('Y-m-d'));
    expect($component->get('startTime'))->toBe('09:00');
    expect($component->get('endDate'))->toBe(now()->addDays(2)->format('Y-m-d'));
    expect($component->get('endTime'))->toBe('17:00');
});

test('component initializes with price by group', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create([
        'price_by_group' => '100.00',
        'price_by_person' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['activity' => $activity]);

    expect($component->get('price'))->toBe('100.00');
    expect($component->get('priceType'))->toBe('price_by_group');
});

test('component initializes with no price', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create([
        'price_by_group' => null,
        'price_by_person' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['activity' => $activity]);

    expect($component->get('price'))->toBeNull();
    expect($component->get('priceType'))->toBe('price_by_person'); // Default
});

test('component loads existing media', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    // Add media to the activity
    $file = UploadedFile::fake()->image('test.jpg');
    $activity->addMedia($file->getRealPath())
        ->usingName('test-image.jpg')
        ->toMediaCollection();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['activity' => $activity]);

    $existingMedia = $component->get('existingMedia');
    expect($existingMedia)->toHaveCount(1);
    expect($existingMedia[0])->toHaveKeys(['id', 'name', 'url', 'file_name', 'marked_for_deletion']);
    expect($existingMedia[0]['name'])->toBe('test-image.jpg');
    expect($existingMedia[0]['marked_for_deletion'])->toBeFalse();
});

test('can update activity with all fields', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($user);

    Livewire::test(Update::class, ['activity' => $activity])
        ->set('name', 'Updated Activity')
        ->set('description', 'Updated Description')
        ->set('url', 'https://updated.com')
        ->set('place', [
            'display_name' => 'Lyon, France',
            'lat' => 45.7640,
            'lng' => 4.8357,
        ])
        ->set('price', 35.00)
        ->set('priceType', 'price_by_person')
        ->set('startDate', now()->addDays(3)->format('Y-m-d'))
        ->set('startTime', '10:00')
        ->set('endDate', now()->addDays(3)->format('Y-m-d'))
        ->set('endTime', '18:00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('activity-updated');

    $activity->refresh();
    expect($activity->name)->toBe('Updated Activity');
    expect($activity->description)->toBe('Updated Description');
    expect($activity->url)->toBe('https://updated.com');
    expect($activity->place_name)->toBe('Lyon, France');
    expect($activity->place_latitude)->toBe('45.764');
    expect($activity->place_longitude)->toBe('4.8357');
    expect($activity->price_by_person)->toBe('35.00');
    expect($activity->price_by_group)->toBeNull();
    expect($activity->start_date->format('Y-m-d'))->toBe(now()->addDays(3)->format('Y-m-d'));
    expect($activity->start_time)->toBe('10:00');
    expect($activity->end_date->format('Y-m-d'))->toBe(now()->addDays(3)->format('Y-m-d'));
    expect($activity->end_time)->toBe('18:00');
});

test('can update activity with price by group', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['activity' => $activity])
        ->set('name', 'Updated Activity')
        ->set('price', 150)
        ->set('priceType', 'price_by_group')
        ->call('save')
        ->assertHasNoErrors();

    $activity->refresh();
    expect($activity->price_by_person)->toBeNull();
    expect($activity->price_by_group)->toBe('150.00');
});

test('validates required name field', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['activity' => $activity])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('validates url field format', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['activity' => $activity])
        ->set('name', 'Test Activity')
        ->set('url', 'invalid-url')
        ->call('save')
        ->assertHasErrors(['url' => 'url']);
});

test('validates price field is numeric', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['activity' => $activity])
        ->set('name', 'Test Activity')
        ->set('price', 'not-a-number')
        ->call('save')
        ->assertHasErrors(['price' => 'numeric']);
});

test('start date update triggers end date refresh', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $startDate = now()->addDays(3)->format('Y-m-d');
    $endDate = now()->addDays(2)->format('Y-m-d');

    $component = Livewire::test(Update::class, ['activity' => $activity])
        ->set('endDate', $endDate) // Set end date first
        ->set('startDate', $startDate); // Then set start date to trigger update

    // End date should be updated to match start date when it's before start date
    expect($component->get('endDate'))->toBe($startDate);
});

test('start date set to null clears end date', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['activity' => $activity])
        ->set('endDate', now()->addDays(3)->format('Y-m-d'))
        ->set('startDate', null);

    expect($component->get('endDate'))->toBeNull();
});

test('can handle file uploads', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->image('test1.jpg');
    $file2 = UploadedFile::fake()->image('test2.jpg');

    $component = Livewire::test(Update::class, ['activity' => $activity])
        ->set('images', [$file1, $file2]);

    // Check that files are moved to tempImages
    expect($component->get('tempImages'))->toHaveCount(2);
    expect($component->get('images'))->toHaveCount(0); // Should be cleared after upload
});

test('can remove uploaded images', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->image('test1.jpg');
    $file2 = UploadedFile::fake()->image('test2.jpg');

    $component = Livewire::test(Update::class, ['activity' => $activity])
        ->set('images', [$file1, $file2])
        ->call('removeImage', 0);

    expect($component->get('tempImages'))->toHaveCount(1);
});

test('can handle remove image with invalid index', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('test.jpg');

    $component = Livewire::test(Update::class, ['activity' => $activity])
        ->set('images', [$file])
        ->call('removeImage', 999); // Invalid index

    // Should not crash and tempImages should remain unchanged
    expect($component->get('tempImages'))->toHaveCount(1);
});

test('can mark existing media for deletion', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    // Add media to the activity
    $file = UploadedFile::fake()->image('test.jpg');
    $media = $activity->addMedia($file->getRealPath())
        ->usingName('test-image.jpg')
        ->toMediaCollection();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['activity' => $activity])
        ->call('markMediaForDeletion', $media->id);

    expect($component->get('mediaToDelete'))->toContain($media->id);

    $existingMedia = $component->get('existingMedia');
    expect($existingMedia[0]['marked_for_deletion'])->toBeTrue();
});

test('can save with new images and delete existing media', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    // Add existing media
    $existingFile = UploadedFile::fake()->image('existing.jpg');
    $existingMedia = $activity->addMedia($existingFile->getRealPath())
        ->usingName('existing-image.jpg')
        ->toMediaCollection();

    $this->actingAs($user);

    $newFile = UploadedFile::fake()->image('new.jpg');

    Livewire::test(Update::class, ['activity' => $activity])
        ->set('name', 'Updated Activity')
        ->set('images', [$newFile])
        ->call('markMediaForDeletion', $existingMedia->id)
        ->call('save')
        ->assertHasNoErrors();

    $activity->refresh();
    expect($activity->name)->toBe('Updated Activity');

    // Check that existing media was deleted and new media was added
    $media = $activity->getMedia();
    expect($media)->toHaveCount(1);
    expect($media->first()->name)->toBe('new.jpg');
});

test('can delete activity', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();
    $activityId = $activity->id;

    $this->actingAs($user);

    Livewire::test(Update::class, ['activity' => $activity])
        ->call('delete')
        ->assertRedirect(route('travel.show', $travel->id));

    expect(Activity::find($activityId))->toBeNull();
});

test('cleanup fields resets form data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create([
        'name' => 'Test Activity',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['activity' => $activity])
        ->set('tempImages', ['fake-image'])
        ->set('mediaToDelete', [123])
        ->call('cleanupFields');

    expect($component->get('tempImages'))->toBeEmpty();
    expect($component->get('images'))->toBeEmpty();
    expect($component->get('mediaToDelete'))->toBeEmpty();
    // Fields should be reinitialized with activity data
    expect($component->get('name'))->toBe('Test Activity');
});

test('handles invalid uploaded images gracefully', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    // Set empty images array
    $component = Livewire::test(Update::class, ['activity' => $activity])
        ->set('images', []);

    // Should not crash and tempImages should remain empty
    expect($component->get('tempImages'))->toBeEmpty();
});

test('handles mount with null activity', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['activity' => null]);

    expect($component->get('activity'))->toBeNull();
    // Component should handle null activity gracefully without calling initFields
});

test('saves activity with null dates when cleared', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $activity = Activity::factory()->forTravel($travel)->create([
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(2),
    ]);

    $this->actingAs($user);

    Livewire::test(Update::class, ['activity' => $activity])
        ->set('name', 'Updated Activity')
        ->set('startDate', null)
        ->set('endDate', null)
        ->call('save')
        ->assertHasNoErrors();

    $activity->refresh();
    expect($activity->start_date)->toBeNull();
    expect($activity->end_date)->toBeNull();
});
