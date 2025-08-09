<?php

use App\Livewire\Travel\Create;
use App\Models\Travel;
use App\Models\User;
use Livewire\Livewire;
use Masmerise\Toaster\Toaster;

test('travel create component can be rendered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->assertStatus(200);
});

test('component initializes with empty data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(Create::class);

    expect($component->get('name'))->toBeNull();
    expect($component->get('place'))->toBe([
        'display_name' => null,
        'lat' => null,
        'lng' => null,
    ]);
    expect($component->get('members'))->toBe([]);
    expect($component->get('dateRange'))->toBe([
        'start' => null,
        'end' => null,
    ]);
});

test('can create travel with required fields only', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Test Travel')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(Travel::where('name', 'Test Travel')->exists())->toBeTrue();

    $travel = Travel::where('name', 'Test Travel')->first();
    expect($travel->name)->toBe('Test Travel');
    expect($travel->place_name)->toBeNull();
    expect($travel->place_latitude)->toBeNull();
    expect($travel->place_longitude)->toBeNull();
    expect($travel->start_date)->toBeNull();
    expect($travel->end_date)->toBeNull();
});

test('can create travel with all fields filled', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(Create::class)
        ->set('name', 'Complete Travel')
        ->set('place', [
            'display_name' => 'Paris, France',
            'lat' => 48.8566,
            'lng' => 2.3522,
        ])
        ->set('dateRange', [
            'start' => '2025-06-01',
            'end' => '2025-06-07',
        ])
        ->set('members', ['test@example.com', 'another@example.com'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $travel = Travel::where('name', 'Complete Travel')->first();
    expect($travel)->not->toBeNull();
    expect($travel->name)->toBe('Complete Travel');
    expect($travel->place_name)->toBe('Paris, France');
    expect($travel->place_latitude)->toBe('48.8566');
    expect($travel->place_longitude)->toBe('2.3522');
    expect($travel->start_date->format('Y-m-d'))->toBe('2025-06-01');
    expect($travel->end_date->format('Y-m-d'))->toBe('2025-06-07');
});

test('validates required name field', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('name field can contain special characters', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Travel with "quotes" & symbols!')
        ->call('save')
        ->assertHasNoErrors();

    expect(Travel::where('name', 'Travel with "quotes" & symbols!')->exists())->toBeTrue();
});

test('travel creation attaches owner', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Owner Test Travel')
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'Owner Test Travel')->first();
    expect($travel->isOwner($user))->toBeTrue();
    expect($travel->members()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('travel creation invites members', function () {
    $user = User::factory()->create();
    $memberEmails = ['member1@example.com', 'member2@example.com'];

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Members Test Travel')
        ->set('members', $memberEmails)
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'Members Test Travel')->first();
    expect($travel->invitations()->count())->toBe(2);
    expect($travel->invitations()->pluck('email')->toArray())->toMatchArray($memberEmails);
});

test('cleanup fields resets all form data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(Create::class)
        ->set('name', 'Test Travel')
        ->set('place', [
            'display_name' => 'Paris, France',
            'lat' => 48.8566,
            'lng' => 2.3522,
        ])
        ->set('dateRange', [
            'start' => '2025-06-01',
            'end' => '2025-06-07',
        ])
        ->set('members', ['test@example.com'])
        ->call('cleanupFields')
        ->assertDispatched('clean-map')
        ->assertDispatched('clean-members')
        ->assertDispatched('clean-date-range');

    expect($component->get('name'))->toBeNull();
    expect($component->get('place'))->toBe([
        'display_name' => null,
        'lat' => null,
        'lng' => null,
    ]);
    expect($component->get('members'))->toBe([]);
    expect($component->get('dateRange'))->toBe([
        'start' => null,
        'end' => null,
    ]);
});

test('can create travel without place information', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'No Place Travel')
        ->set('place', [
            'display_name' => null,
            'lat' => null,
            'lng' => null,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'No Place Travel')->first();
    expect($travel->place_name)->toBeNull();
    expect($travel->place_latitude)->toBeNull();
    expect($travel->place_longitude)->toBeNull();
});

test('can create travel without date range', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'No Date Travel')
        ->set('dateRange', [
            'start' => null,
            'end' => null,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'No Date Travel')->first();
    expect($travel->start_date)->toBeNull();
    expect($travel->end_date)->toBeNull();
});

test('can create travel with only start date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Start Only Travel')
        ->set('dateRange', [
            'start' => '2025-06-01',
            'end' => null,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'Start Only Travel')->first();
    expect($travel->start_date->format('Y-m-d'))->toBe('2025-06-01');
    expect($travel->end_date)->toBeNull();
});

test('can create travel with only end date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'End Only Travel')
        ->set('dateRange', [
            'start' => null,
            'end' => '2025-06-07',
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'End Only Travel')->first();
    expect($travel->start_date)->toBeNull();
    expect($travel->end_date->format('Y-m-d'))->toBe('2025-06-07');
});

test('can create travel without members', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Solo Travel')
        ->set('members', [])
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'Solo Travel')->first();
    expect($travel->invitations()->count())->toBe(0);
    expect($travel->members()->count())->toBe(1); // Only the owner
});

test('redirects to travel show page after creation', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = Livewire::test(Create::class)
        ->set('name', 'Redirect Test Travel')
        ->call('save');

    $travel = Travel::where('name', 'Redirect Test Travel')->first();
    $response->assertRedirect(route('travel.show', ['travelId' => $travel->id]));
});

test('unauthenticated user cannot create travel', function () {
    $this->expectException(\TypeError::class);

    Livewire::test(Create::class)
        ->set('name', 'Unauthorized Travel')
        ->call('save');
});

test('can handle empty members array', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Empty Members Travel')
        ->set('members', [])
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'Empty Members Travel')->first();
    expect($travel->invitations()->count())->toBe(0);
});

test('handles partial place information', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Partial Place Travel')
        ->set('place', [
            'display_name' => 'Paris, France',
            'lat' => null,
            'lng' => null,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'Partial Place Travel')->first();
    expect($travel->place_name)->toBe('Paris, France');
    expect($travel->place_latitude)->toBeNull();
    expect($travel->place_longitude)->toBeNull();
});

test('handles coordinates without display name', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'Coordinates Only Travel')
        ->set('place', [
            'display_name' => null,
            'lat' => 48.8566,
            'lng' => 2.3522,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel = Travel::where('name', 'Coordinates Only Travel')->first();
    expect($travel->place_name)->toBeNull();
    expect($travel->place_latitude)->toBe('48.8566');
    expect($travel->place_longitude)->toBe('2.3522');
});
