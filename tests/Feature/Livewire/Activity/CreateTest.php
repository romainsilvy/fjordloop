<?php

use App\Livewire\Activity\Create;
use App\Models\Activity;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('activity create component can be rendered', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
        ->assertStatus(200);
});

test('component initializes with travel and date ranges', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(3),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Create::class, ['travel' => $travel]);

    expect($component->get('travel'))->toEqual($travel);
    expect($component->get('travelDateRange'))->toBeArray();
    expect($component->get('availableStartDates'))->toBeArray();
    expect($component->get('availableEndDates'))->toBeArray();
    expect($component->get('priceType'))->toBe('price_by_person');
});

test('can create activity with required fields only', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Activity')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('activityCreated');

    expect(Activity::where('name', 'Test Activity')->exists())->toBeTrue();

    $activity = Activity::where('name', 'Test Activity')->first();
    expect($activity->travel_id)->toBe($travel->id);
    expect($activity->description)->toBeNull();
    expect($activity->url)->toBeNull();
    expect($activity->price_by_person)->toBeNull();
    expect($activity->price_by_group)->toBeNull();
});

test('can create activity with all fields filled', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Complete Activity')
        ->set('description', 'This is a test activity description')
        ->set('url', 'https://example.com')
        ->set('place', [
            'display_name' => 'Paris, France',
            'lat' => 48.8566,
            'lng' => 2.3522,
        ])
        ->set('price', 25.50)
        ->set('priceType', 'price_by_person')
        ->set('startDate', now()->addDays(2)->format('Y-m-d'))
        ->set('startTime', '09:00')
        ->set('endDate', now()->addDays(2)->format('Y-m-d'))
        ->set('endTime', '17:00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('activityCreated');

    $activity = Activity::where('name', 'Complete Activity')->first();
    expect($activity)->not->toBeNull();
    expect($activity->description)->toBe('This is a test activity description');
    expect($activity->url)->toBe('https://example.com');
    expect($activity->place_name)->toBe('Paris, France');
    expect($activity->place_latitude)->toBe('48.8566');
    expect($activity->place_longitude)->toBe('2.3522');
    expect($activity->price_by_person)->toBe('25.50');
    expect($activity->price_by_group)->toBeNull();
    expect($activity->start_date->format('Y-m-d'))->toBe(now()->addDays(2)->format('Y-m-d'));
    expect($activity->start_time)->toBe('09:00');
    expect($activity->end_date->format('Y-m-d'))->toBe(now()->addDays(2)->format('Y-m-d'));
    expect($activity->end_time)->toBe('17:00');
});

test('can create activity with price by group', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Group Activity')
        ->set('price', 100)
        ->set('priceType', 'price_by_group')
        ->call('save')
        ->assertHasNoErrors();

    $activity = Activity::where('name', 'Group Activity')->first();
    expect($activity->price_by_person)->toBeNull();
    expect($activity->price_by_group)->toBe('100.00');
});

test('validates required name field', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
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

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
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

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Activity')
        ->set('price', 'not-a-number')
        ->call('save')
        ->assertHasErrors(['price' => 'numeric']);
});

test('url field can be null', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Activity')
        ->set('url', null)
        ->call('save')
        ->assertHasNoErrors();
});

test('price field can be null', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Activity')
        ->set('price', null)
        ->call('save')
        ->assertHasNoErrors();
});

test('start date update triggers end date refresh', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $startDate = now()->addDays(3)->format('Y-m-d');
    $endDate = now()->addDays(2)->format('Y-m-d');

    $component = Livewire::test(Create::class, ['travel' => $travel])
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

    $this->actingAs($user);

    $component = Livewire::test(Create::class, ['travel' => $travel])
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

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->image('test1.jpg');
    $file2 = UploadedFile::fake()->image('test2.jpg');

    $component = Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Activity with Images')
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

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->image('test1.jpg');
    $file2 = UploadedFile::fake()->image('test2.jpg');

    $component = Livewire::test(Create::class, ['travel' => $travel])
        ->set('images', [$file1, $file2])
        ->call('removeImage', 0);

    expect($component->get('tempImages'))->toHaveCount(1);
});

test('cleanup fields resets all form data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Activity')
        ->set('description', 'Test Description')
        ->set('url', 'https://example.com')
        ->set('price', 25)
        ->set('startDate', now()->addDays(2)->format('Y-m-d'))
        ->set('startTime', '09:00')
        ->set('endDate', now()->addDays(2)->format('Y-m-d'))
        ->set('endTime', '17:00')
        ->call('cleanupFields')
        ->assertDispatched('clean-map');

    expect($component->get('name'))->toBeNull();
    expect($component->get('description'))->toBeNull();
    expect($component->get('url'))->toBeNull();
    expect($component->get('price'))->toBeNull();
    expect($component->get('priceType'))->toBe('price_by_person');
    expect($component->get('startDate'))->toBeNull();
    expect($component->get('startTime'))->toBeNull();
    expect($component->get('endDate'))->toBeNull();
    expect($component->get('endTime'))->toBeNull();
    expect($component->get('tempImages'))->toBeEmpty();
    expect($component->get('images'))->toBeEmpty();
});

test('save creates activity and cleans up fields', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Activity')
        ->set('description', 'Test Description')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('activityCreated')
        ->assertDispatched('clean-map');

    // Verify activity was created
    expect(Activity::where('name', 'Test Activity')->exists())->toBeTrue();

    // Verify fields were cleaned up
    expect($component->get('name'))->toBeNull();
    expect($component->get('description'))->toBeNull();
});

test('unauthenticated user cannot create activity', function () {
    $owner = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    // Test que la policy rejette correctement un utilisateur non authentifié
    expect(fn () => Gate::authorize('createActivity', $travel))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

test('user must be travel member to access travel', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($otherUser);

    // Non-member cannot find the travel due to global scope
    expect(Travel::find($travel->id))->toBeNull();
});

test('can save activity with images and handle media uploads', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('test.jpg');

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Activity with Image')
        ->set('images', [$file])
        ->call('save')
        ->assertHasNoErrors();

    $activity = Activity::where('name', 'Activity with Image')->first();
    expect($activity)->not->toBeNull();

    // Check that media was attached (this tests the media upload loop in save method)
    expect($activity->getMedia())->not->toBeEmpty();
});

test('can handle remove image with invalid index', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('test.jpg');

    $component = Livewire::test(Create::class, ['travel' => $travel])
        ->set('images', [$file])
        ->call('removeImage', 999); // Invalid index

    // Should not crash and tempImages should remain unchanged
    expect($component->get('tempImages'))->toHaveCount(1);
});
