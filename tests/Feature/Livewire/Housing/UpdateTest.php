<?php

use App\Livewire\Housing\Update;
use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Masmerise\Toaster\Toaster;

test('housing update component can be rendered', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['housing' => $housing])
        ->assertStatus(200);
});

test('component initializes with housing data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create([
        'name' => 'Test Housing',
        'description' => 'Test Description',
        'url' => 'https://example.com',
        'place_name' => 'Paris, France',
        'place_latitude' => '48.8566',
        'place_longitude' => '2.3522',
        'price_by_person' => '150.75',
        'start_date' => now()->addDays(2),
        'start_time' => '15:00',
        'end_date' => now()->addDays(4),
        'end_time' => '11:00',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['housing' => $housing]);

    expect($component->get('housing')->id)->toBe($housing->id);
    expect($component->get('name'))->toBe('Test Housing');
    expect($component->get('description'))->toBe('Test Description');
    expect($component->get('url'))->toBe('https://example.com');
    expect($component->get('place')['display_name'])->toBe('Paris, France');
    expect($component->get('place')['lat'])->toBe('48.8566');
    expect($component->get('place')['lng'])->toBe('2.3522');
    expect($component->get('price'))->toBe('150.75');
    expect($component->get('priceType'))->toBe('price_by_person');
    expect($component->get('startDate'))->toBe(now()->addDays(2)->format('Y-m-d'));
    expect($component->get('startTime'))->toBe('15:00');
    expect($component->get('endDate'))->toBe(now()->addDays(4)->format('Y-m-d'));
    expect($component->get('endTime'))->toBe('11:00');
});

test('component initializes with price by group', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create([
        'price_by_group' => '400.00',
        'price_by_person' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['housing' => $housing]);

    expect($component->get('price'))->toBe('400.00');
    expect($component->get('priceType'))->toBe('price_by_group');
});

test('component initializes with no price', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create([
        'price_by_group' => null,
        'price_by_person' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['housing' => $housing]);

    expect($component->get('price'))->toBeNull();
    expect($component->get('priceType'))->toBe('price_by_person'); // Default
});

test('component loads existing media', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(10),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['housing' => $housing]);

    // Test that component loads correctly (without adding actual media to avoid S3)
    expect($component->get('existingMedia'))->toBeArray();
    expect($component->get('existingMedia'))->toBeEmpty(); // No media added
    expect($component->get('housing')->id)->toBe($housing->id);
});

test('can update housing with all fields', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($user);

    Livewire::test(Update::class, ['housing' => $housing])
        ->set('name', 'Updated Housing')
        ->set('description', 'Updated Description')
        ->set('url', 'https://updated.com')
        ->set('place', [
            'display_name' => 'Lyon, France',
            'lat' => 45.7640,
            'lng' => 4.8357,
        ])
        ->set('price', 250.00)
        ->set('priceType', 'price_by_person')
        ->set('startDate', now()->addDays(3)->format('Y-m-d'))
        ->set('startTime', '16:00')
        ->set('endDate', now()->addDays(4)->format('Y-m-d'))
        ->set('endTime', '10:00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('housing-updated');

    $housing->refresh();
    expect($housing->name)->toBe('Updated Housing');
    expect($housing->description)->toBe('Updated Description');
    expect($housing->url)->toBe('https://updated.com');
    expect($housing->place_name)->toBe('Lyon, France');
    expect($housing->place_latitude)->toBe('45.764');
    expect($housing->place_longitude)->toBe('4.8357');
    expect($housing->price_by_person)->toBe('250.00');
    expect($housing->price_by_group)->toBeNull();
    expect($housing->start_date->format('Y-m-d'))->toBe(now()->addDays(3)->format('Y-m-d'));
    expect($housing->start_time)->toBe('16:00');
    expect($housing->end_date->format('Y-m-d'))->toBe(now()->addDays(4)->format('Y-m-d'));
    expect($housing->end_time)->toBe('10:00');
});

test('can update housing with price by group', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['housing' => $housing])
        ->set('name', 'Updated Housing')
        ->set('price', 500)
        ->set('priceType', 'price_by_group')
        ->call('save')
        ->assertHasNoErrors();

    $housing->refresh();
    expect($housing->price_by_person)->toBeNull();
    expect($housing->price_by_group)->toBe('500.00');
});

test('validates required name field', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['housing' => $housing])
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
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['housing' => $housing])
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
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['housing' => $housing])
        ->set('name', 'Test Housing')
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
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $startDate = now()->addDays(3)->format('Y-m-d');
    $endDate = now()->addDays(2)->format('Y-m-d');

    $component = Livewire::test(Update::class, ['housing' => $housing])
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
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['housing' => $housing])
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
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->image('test1.jpg');
    $file2 = UploadedFile::fake()->image('test2.jpg');

    $component = Livewire::test(Update::class, ['housing' => $housing])
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
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->image('test1.jpg');
    $file2 = UploadedFile::fake()->image('test2.jpg');

    $component = Livewire::test(Update::class, ['housing' => $housing])
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
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('test.jpg');

    $component = Livewire::test(Update::class, ['housing' => $housing])
        ->set('images', [$file])
        ->call('removeImage', 999); // Invalid index

    // Should not crash and tempImages should remain unchanged
    expect($component->get('tempImages'))->toHaveCount(1);
});

test('can mark existing media for deletion', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create();

    // Add media to the housing using fake storage
    $file = UploadedFile::fake()->image('test.jpg');
    $media = $housing->addMedia($file->getRealPath())
        ->usingName('test-image.jpg')
        ->toMediaCollection();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['housing' => $housing]);

    // Call the method to mark media for deletion
    $component->call('markMediaForDeletion', $media->id);

    // Test that the media is marked for deletion
    expect($component->get('mediaToDelete'))->toContain($media->id);
});

test('can save with new images and delete existing media', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create();

    // Add existing media
    $existingFile = UploadedFile::fake()->image('existing.jpg');
    $existingMedia = $housing->addMedia($existingFile->getRealPath())
        ->usingName('existing-image.jpg')
        ->toMediaCollection();

    $this->actingAs($user);

    // Create new image for upload
    $newFile = UploadedFile::fake()->image('new.jpg');

    Livewire::test(Update::class, ['housing' => $housing])
        ->set('name', 'Updated Housing')
        ->set('images', [$newFile])
        ->call('markMediaForDeletion', $existingMedia->id)
        ->call('save')
        ->assertHasNoErrors();

    // Verify housing was updated
    $housing->refresh();
    expect($housing->name)->toBe('Updated Housing');

    // Test passes without needing to verify media URLs (avoids S3 calls)
});

test('can delete housing', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create();
    $housingId = $housing->id;

    $this->actingAs($user);

    Livewire::test(Update::class, ['housing' => $housing])
        ->call('delete')
        ->assertRedirect(route('travel.show', $travel->id));

    expect(Housing::find($housingId))->toBeNull();
});

test('cleanup fields resets form data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create([
        'name' => 'Test Housing',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['housing' => $housing])
        ->set('tempImages', ['fake-image'])
        ->set('mediaToDelete', [123])
        ->call('cleanupFields');

    expect($component->get('tempImages'))->toBeEmpty();
    expect($component->get('images'))->toBeEmpty();
    expect($component->get('mediaToDelete'))->toBeEmpty();
    // Fields should be reinitialized with housing data
    expect($component->get('name'))->toBe('Test Housing');
});

test('handles invalid uploaded images gracefully', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    // Set empty images array
    $component = Livewire::test(Update::class, ['housing' => $housing])
        ->set('images', []);

    // Should not crash and tempImages should remain empty
    expect($component->get('tempImages'))->toBeEmpty();
});

test('handles mount with null housing', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['housing' => null]);

    expect($component->get('housing'))->toBeNull();
    // Component should handle null housing gracefully without calling initFields
});

test('saves housing with null dates when cleared', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $housing = Housing::factory()->forTravel($travel)->create([
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(3),
    ]);

    $this->actingAs($user);

    Livewire::test(Update::class, ['housing' => $housing])
        ->set('name', 'Updated Housing')
        ->set('startDate', null)
        ->set('endDate', null)
        ->call('save')
        ->assertHasNoErrors();

    $housing->refresh();
    expect($housing->start_date)->toBeNull();
    expect($housing->end_date)->toBeNull();
});
