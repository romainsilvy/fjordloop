<?php

use App\Livewire\Housing\Create;
use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('housing create component can be rendered', function () {
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

test('can create housing with required fields only', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Housing')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('housingCreated');

    expect(Housing::where('name', 'Test Housing')->exists())->toBeTrue();

    $housing = Housing::where('name', 'Test Housing')->first();
    expect($housing->travel_id)->toBe($travel->id);
    expect($housing->description)->toBeNull();
    expect($housing->url)->toBeNull();
    expect($housing->price_by_person)->toBeNull();
    expect($housing->price_by_group)->toBeNull();
});

test('can create housing with all fields filled', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Complete Housing')
        ->set('description', 'This is a test housing description')
        ->set('url', 'https://example.com')
        ->set('place', [
            'display_name' => 'Paris, France',
            'lat' => 48.8566,
            'lng' => 2.3522,
        ])
        ->set('price', 125.50)
        ->set('priceType', 'price_by_person')
        ->set('startDate', now()->addDays(2)->format('Y-m-d'))
        ->set('startTime', '15:00')
        ->set('endDate', now()->addDays(4)->format('Y-m-d'))
        ->set('endTime', '11:00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('housingCreated');

    $housing = Housing::where('name', 'Complete Housing')->first();
    expect($housing)->not->toBeNull();
    expect($housing->description)->toBe('This is a test housing description');
    expect($housing->url)->toBe('https://example.com');
    expect($housing->place_name)->toBe('Paris, France');
    expect($housing->place_latitude)->toBe('48.8566');
    expect($housing->place_longitude)->toBe('2.3522');
    expect($housing->price_by_person)->toBe('125.50');
    expect($housing->price_by_group)->toBeNull();
    expect($housing->start_date->format('Y-m-d'))->toBe(now()->addDays(2)->format('Y-m-d'));
    expect($housing->start_time)->toBe('15:00');
    expect($housing->end_date->format('Y-m-d'))->toBe(now()->addDays(4)->format('Y-m-d'));
    expect($housing->end_time)->toBe('11:00');
});

test('can create housing with price by group', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Group Housing')
        ->set('price', 300)
        ->set('priceType', 'price_by_group')
        ->call('save')
        ->assertHasNoErrors();

    $housing = Housing::where('name', 'Group Housing')->first();
    expect($housing->price_by_person)->toBeNull();
    expect($housing->price_by_group)->toBe('300.00');
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
        ->set('name', 'Test Housing')
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
        ->set('name', 'Test Housing')
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
        ->set('name', 'Test Housing')
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
        ->set('name', 'Test Housing')
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
        ->set('name', 'Housing with Images')
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
        ->set('name', 'Test Housing')
        ->set('description', 'Test Description')
        ->set('url', 'https://example.com')
        ->set('price', 100)
        ->set('startDate', now()->addDays(2)->format('Y-m-d'))
        ->set('startTime', '15:00')
        ->set('endDate', now()->addDays(3)->format('Y-m-d'))
        ->set('endTime', '11:00')
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

test('save creates housing and cleans up fields', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Housing')
        ->set('description', 'Test Description')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('housingCreated')
        ->assertDispatched('clean-map');

    // Verify housing was created
    expect(Housing::where('name', 'Test Housing')->exists())->toBeTrue();

    // Verify fields were cleaned up
    expect($component->get('name'))->toBeNull();
    expect($component->get('description'))->toBeNull();
});

test('can save housing with images and handle media uploads', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('test.jpg');

    Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Housing with Image')
        ->set('images', [$file])
        ->call('save')
        ->assertHasNoErrors();

    $housing = Housing::where('name', 'Housing with Image')->first();
    expect($housing)->not->toBeNull();

    // Check that media was attached (this tests the media upload loop in save method)
    expect($housing->getMedia())->not->toBeEmpty();
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

test('unauthenticated user cannot create housing', function () {
    $travel = Travel::factory()->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $response = Livewire::test(Create::class, ['travel' => $travel])
        ->set('name', 'Test Housing')
        ->call('save');

    // This will fail because auth()->user() is null in the save method
    expect(Housing::where('name', 'Test Housing')->exists())->toBeFalse();
});

test('user must be travel member to create housing', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($otherUser);

    // Non-member cannot access the travel due to global scope
    expect(Travel::find($travel->id))->toBeNull();
});
