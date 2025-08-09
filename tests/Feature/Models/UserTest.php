<?php

use App\Models\User;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User model', function () {

    it('can be created with fillable fields', function () {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        expect($user->name)->toBe('John Doe')
            ->and($user->email)->toBe('john@example.com')
            ->and($user->email_verified_at)->not->toBeNull();
    });

    it('can retrieve owned travels', function () {
        $user = User::factory()->create();
        $ownedTravel = Travel::factory()->withOwner($user)->create();
        $memberTravel = Travel::factory()->create();

        // Add user as a non-owner member to another travel
        $memberTravel->members()->attach($user->id, ['is_owner' => false]);

        $ownedTravels = $user->ownedTravels;

        expect($ownedTravels)->toHaveCount(1)
            ->and($ownedTravels->first()->id)->toBe($ownedTravel->id);
    });

    it('can retrieve all travels (owned and member)', function () {
        $user = User::factory()->create();
        $ownedTravel = Travel::factory()->withOwner($user)->create();
        $memberTravel = Travel::factory()->create();

        // Add user as a non-owner member to another travel
        $memberTravel->members()->attach($user->id, ['is_owner' => false]);

        $allTravels = $user->travels;

        expect($allTravels)->toHaveCount(2)
            ->and($allTravels->pluck('id'))->toContain($ownedTravel->id)
            ->and($allTravels->pluck('id'))->toContain($memberTravel->id);
    });

    it('returns empty collection when user has no travels', function () {
        $user = User::factory()->create();

        expect($user->ownedTravels)->toBeEmpty()
            ->and($user->travels)->toBeEmpty();
    });

    it('maintains pivot data for travel relationships', function () {
        $user = User::factory()->create();
        $ownedTravel = Travel::factory()->withOwner($user)->create();
        $memberTravel = Travel::factory()->create();

        // Add user as a non-owner member
        $memberTravel->members()->attach($user->id, ['is_owner' => false]);

        $ownedTravels = $user->ownedTravels;
        $allTravels = $user->travels;

        // Check pivot data for owned travel
        expect($ownedTravels->first()->pivot->is_owner)->toBeTrue();

        // Check pivot data for all travels
        $ownedTravelFromAll = $allTravels->where('id', $ownedTravel->id)->first();
        $memberTravelFromAll = $allTravels->where('id', $memberTravel->id)->first();

        expect($ownedTravelFromAll->pivot->is_owner)->toBeTrue()
            ->and($memberTravelFromAll->pivot->is_owner)->toBeFalse();
    });

    it('can distinguish between owned and member travels', function () {
        $user = User::factory()->create();

        // Create multiple travels with different ownership
        $ownedTravel1 = Travel::factory()->withOwner($user)->create();
        $ownedTravel2 = Travel::factory()->withOwner($user)->create();
        $memberTravel1 = Travel::factory()->create();
        $memberTravel2 = Travel::factory()->create();

        // Add user as member (non-owner) to other travels
        $memberTravel1->members()->attach($user->id, ['is_owner' => false]);
        $memberTravel2->members()->attach($user->id, ['is_owner' => false]);

        $ownedTravels = $user->ownedTravels;
        $allTravels = $user->travels;

        expect($ownedTravels)->toHaveCount(2)
            ->and($allTravels)->toHaveCount(4)
            ->and($ownedTravels->pluck('id'))->toContain($ownedTravel1->id)
            ->and($ownedTravels->pluck('id'))->toContain($ownedTravel2->id)
            ->and($ownedTravels->pluck('id'))->not->toContain($memberTravel1->id)
            ->and($ownedTravels->pluck('id'))->not->toContain($memberTravel2->id);
    });

    it('can generate initials from name', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        expect($user->initials())->toBe('JD');

        $user = User::factory()->create(['name' => 'Jean Pierre Marie']);
        expect($user->initials())->toBe('JPM');

        $user = User::factory()->create(['name' => 'Alice']);
        expect($user->initials())->toBe('A');

        $user = User::factory()->create(['name' => '']);
        expect($user->initials())->toBe('');
    });

});
