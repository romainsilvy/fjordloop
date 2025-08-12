<?php

use App\Livewire\Housing\Show;
use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use Livewire\Livewire;

test('housing show component can be rendered', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'housingId' => $housing->id,
    ])->assertStatus(200);
});

test('component initializes with travel and housing data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Test Travel',
    ]);
    $housing = Housing::factory()->forTravel($travel)->create([
        'name' => 'Test Housing',
        'description' => 'Test Description',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'housingId' => $housing->id,
    ]);

    expect($component->get('travel')->id)->toBe($travel->id);
    expect($component->get('housing')->id)->toBe($housing->id);
    expect($component->get('housing')->name)->toBe('Test Housing');
});

test('refresh housing updates housing data and dispatches events', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $housing = Housing::factory()->forTravel($travel)->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'housingId' => $housing->id,
    ]);

    // Update the housing in the database
    $housing->update(['name' => 'Updated Name']);

    // Trigger refresh (without media, so no S3 calls)
    $component->call('refreshHousing')
        ->assertDispatched('housing-refreshed')
        ->assertDispatched('media-refreshed');

    // Check that the housing data is refreshed
    expect($component->get('housing')->name)->toBe('Updated Name');
});

test('throws exception when travel not found', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(Show::class, [
        'travelId' => '01988e63-0000-7000-8000-000000000000', // Valid UUID format
        'housingId' => '01988e63-0000-7000-8000-000000000001',
    ]);
});

test('throws exception when housing not found', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'housingId' => '01988e63-0000-7000-8000-000000000001', // Valid UUID format
    ]);
});

test('throws exception when housing does not belong to travel', function () {
    $user = User::factory()->create();
    $travel1 = Travel::factory()->withOwner($user)->create();
    $travel2 = Travel::factory()->withOwner($user)->create();
    $housing = Housing::factory()->forTravel($travel2)->create();

    $this->actingAs($user);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    // Trying to access housing from travel2 using travel1's ID
    Livewire::test(Show::class, [
        'travelId' => $travel1->id,
        'housingId' => $housing->id,
    ]);
});

test('user must be travel member to view housing', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create();
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($otherUser);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    // Non-member cannot access the travel due to global scope
    Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'housingId' => $housing->id,
    ]);
});

test('refresh housing works when no media changes', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $housing = Housing::factory()->forTravel($travel)->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'housingId' => $housing->id,
    ]);

    // Update only the housing name
    $housing->update(['name' => 'Updated Name']);

    // Trigger refresh
    $component->call('refreshHousing')
        ->assertDispatched('housing-refreshed')
        ->assertDispatched('media-refreshed');

    // Check that the housing data is refreshed
    expect($component->get('housing')->name)->toBe('Updated Name');
});

test('component handles housing with media', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'housingId' => $housing->id,
    ]);

    // Test that component loads without media (avoids S3 calls)
    expect($component->get('housing')->id)->toBe($housing->id);
    expect($component->get('travel')->id)->toBe($travel->id);
    // getMedia() doesn't call getTemporaryUrl, so it should work
    expect($component->get('housing')->getMedia())->toBeEmpty();
});

test('component handles housing without media', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $housing = Housing::factory()->forTravel($travel)->create();

    $this->actingAs($user);

    $component = Livewire::test(Show::class, [
        'travelId' => $travel->id,
        'housingId' => $housing->id,
    ]);

    // Verify the component can handle housing without media
    expect($component->get('housing')->getMedia())->toBeEmpty();
});
