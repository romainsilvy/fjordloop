<?php

use App\Livewire\Travel\Update;
use App\Models\Travel;
use App\Models\TravelInvitation;
use App\Models\User;
use Livewire\Livewire;
use Masmerise\Toaster\Toaster;

test('travel update component can be rendered', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->assertStatus(200);
});

test('component initializes with travel data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Test Travel',
        'place_name' => 'Paris, France',
        'place_latitude' => '48.8566',
        'place_longitude' => '2.3522',
        'start_date' => '2025-06-01',
        'end_date' => '2025-06-07',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel]);

    expect($component->get('travel')->id)->toBe($travel->id);
    expect($component->get('name'))->toBe('Test Travel');
    expect($component->get('place')['display_name'])->toBe('Paris, France');
    expect($component->get('place')['lat'])->toBe('48.8566');
    expect($component->get('place')['lng'])->toBe('2.3522');
    expect($component->get('dateRange')['start'])->toBeInstanceOf(\Carbon\Carbon::class);
    expect($component->get('dateRange')['end'])->toBeInstanceOf(\Carbon\Carbon::class);
    expect($component->get('membersToInvite'))->toBe([]);
});

test('component loads invitations and members', function () {
    $user = User::factory()->create();
    $member = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Add a member to the travel
    $travel->members()->attach($member);

    // Create an invitation
    $invitation = TravelInvitation::factory()->create([
        'travel_id' => $travel->id,
        'email' => 'invited@example.com',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel]);

    expect($component->get('invitations'))->toHaveCount(1);
    expect($component->get('invitations')->first()->email)->toBe('invited@example.com');
    expect($component->get('members'))->toHaveCount(2); // Owner + member
});

test('can update travel with all fields', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', 'Updated Travel')
        ->set('place', [
            'display_name' => 'Lyon, France',
            'lat' => 45.7640,
            'lng' => 4.8357,
        ])
        ->set('dateRange', [
            'start' => '2025-07-01',
            'end' => '2025-07-07',
        ])
        ->set('membersToInvite', ['new@example.com'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('travel.show', ['travelId' => $travel->id]));

    $travel->refresh();
    expect($travel->name)->toBe('Updated Travel');
    expect($travel->place_name)->toBe('Lyon, France');
    expect($travel->place_latitude)->toBe('45.764');
    expect($travel->place_longitude)->toBe('4.8357');
    expect($travel->start_date->format('Y-m-d'))->toBe('2025-07-01');
    expect($travel->end_date->format('Y-m-d'))->toBe('2025-07-07');
});

test('validates required name field', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('can update travel without changing dates', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Original Name',
        'start_date' => '2025-06-01',
        'end_date' => '2025-06-07',
    ]);

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', 'Updated Name Only')
        ->call('save')
        ->assertHasNoErrors();

    $travel->refresh();
    expect($travel->name)->toBe('Updated Name Only');
    expect($travel->start_date->format('Y-m-d'))->toBe('2025-06-01');
    expect($travel->end_date->format('Y-m-d'))->toBe('2025-06-07');
});

test('can clear place information', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'place_name' => 'Paris, France',
        'place_latitude' => '48.8566',
        'place_longitude' => '2.3522',
    ]);

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', 'Updated Travel')
        ->set('place', [
            'display_name' => null,
            'lat' => null,
            'lng' => null,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel->refresh();
    expect($travel->place_name)->toBeNull();
    expect($travel->place_latitude)->toBeNull();
    expect($travel->place_longitude)->toBeNull();
});

test('can clear dates', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'start_date' => '2025-06-01',
        'end_date' => '2025-06-07',
    ]);

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', 'Updated Travel')
        ->set('dateRange', [
            'start' => null,
            'end' => null,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel->refresh();
    expect($travel->start_date)->toBeNull();
    expect($travel->end_date)->toBeNull();
});

test('can invite new members', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', 'Test Travel')
        ->set('membersToInvite', ['new1@example.com', 'new2@example.com'])
        ->call('save')
        ->assertHasNoErrors();

    // Check that invitations were created
    expect($travel->invitations()->count())->toBe(2);
    expect($travel->invitations()->pluck('email')->toArray())->toMatchArray(['new1@example.com', 'new2@example.com']);
});

test('can delete member', function () {
    $user = User::factory()->create();
    $member = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    // Add member to travel
    $travel->members()->attach($member);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel])
        ->call('deleteMember', $member->id);

    // Check that member was removed
    expect($travel->members()->where('user_id', $member->id)->exists())->toBeFalse();
    expect($component->get('members'))->toHaveCount(1); // Only owner remains
});

test('can delete invitation', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $invitation = TravelInvitation::factory()->create([
        'travel_id' => $travel->id,
        'email' => 'test@example.com',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel])
        ->call('deleteInvitation', $invitation->id);

    // Check that invitation was deleted
    expect(TravelInvitation::find($invitation->id))->toBeNull();
    expect($component->get('invitations'))->toBeEmpty();
});

test('can resend invitation', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $invitation = TravelInvitation::factory()->create([
        'travel_id' => $travel->id,
        'email' => 'test@example.com',
    ]);

    $this->actingAs($user);

    // Mock the sendEmail method or expect no errors
    Livewire::test(Update::class, ['travel' => $travel])
        ->call('resendInvitation', $invitation->id);

    // Invitation should still exist
    expect(TravelInvitation::find($invitation->id))->not->toBeNull();
});

test('resend invitation handles non-existent invitation', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel])
        ->call('resendInvitation', '01988e63-0000-7000-8000-000000000001'); // Non-existent UUID

    // Should not crash and should handle gracefully
    // The component should still render properly after the failed operation
    expect($component->get('travel')->id)->toBe($travel->id);
    expect($component->get('invitations'))->toBeEmpty(); // No invitations exist
});

test('cleanup fields reloads travel data', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', 'Modified Name')
        ->set('membersToInvite', ['test@example.com'])
        ->call('cleanupFields');

    // Fields should be reset to original travel data
    expect($component->get('name'))->toBe('Original Name');
    expect($component->get('membersToInvite'))->toBe([]);
});

test('component handles travel with null values', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Test Travel',
        'place_name' => null,
        'place_latitude' => null,
        'place_longitude' => null,
        'start_date' => null,
        'end_date' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel]);

    expect($component->get('name'))->toBe('Test Travel');
    expect($component->get('place')['display_name'])->toBeNull();
    expect($component->get('place')['lat'])->toBeNull();
    expect($component->get('place')['lng'])->toBeNull();
    expect($component->get('dateRange')['start'])->toBeNull();
    expect($component->get('dateRange')['end'])->toBeNull();
});

test('component loads fields on mount', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create([
        'name' => 'Mount Test Travel',
    ]);

    $this->actingAs($user);

    // The mount method should automatically call loadFields
    $component = Livewire::test(Update::class, ['travel' => $travel]);

    expect($component->get('name'))->toBe('Mount Test Travel');
});

test('update with partial place information', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', 'Partial Place Travel')
        ->set('place', [
            'display_name' => 'Paris, France',
            'lat' => null,
            'lng' => null,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $travel->refresh();
    expect($travel->place_name)->toBe('Paris, France');
    expect($travel->place_latitude)->toBeNull();
    expect($travel->place_longitude)->toBeNull();
});

test('can update travel without inviting new members', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();
    $initialInvitationCount = $travel->invitations()->count();

    $this->actingAs($user);

    Livewire::test(Update::class, ['travel' => $travel])
        ->set('name', 'Updated Without Invites')
        ->set('membersToInvite', [])
        ->call('save')
        ->assertHasNoErrors();

    // No new invitations should be created
    expect($travel->invitations()->count())->toBe($initialInvitationCount);
});

test('handles deletion of non-existent member gracefully', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel])
        ->call('deleteMember', '01988e63-0000-7000-8000-000000000001'); // Non-existent user UUID

    // Should not crash
    expect($component->get('members'))->toHaveCount(1); // Only owner
});

test('handles deletion of non-existent invitation gracefully', function () {
    $user = User::factory()->create();
    $travel = Travel::factory()->withOwner($user)->create();

    $this->actingAs($user);

    $component = Livewire::test(Update::class, ['travel' => $travel])
        ->call('deleteInvitation', '01988e63-0000-7000-8000-000000000001'); // Non-existent invitation UUID

    // Should not crash
    expect($component->get('invitations'))->toBeEmpty();
});
