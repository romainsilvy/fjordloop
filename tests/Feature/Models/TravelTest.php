<?php

use App\Models\Activity;
use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

describe('Travel model', function () {

    it('can be created with fillable fields', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create([
            'name' => 'Test Travel',
            'place_name' => 'Paris',
            'place_latitude' => 48.8566,
            'place_longitude' => 2.3522,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(10),
        ]);

        expect($travel->name)->toBe('Test Travel')
            ->and($travel->place_name)->toBe('Paris')
            ->and($travel->place_latitude)->toBe(48.8566)
            ->and($travel->place_longitude)->toBe(2.3522)
            ->and($travel->start_date)->not->toBeNull()
            ->and($travel->end_date)->not->toBeNull();
    });

    it('can attach and retrieve members and owner', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->create();
        $travel->attachOwner($owner);
        $travel->members()->attach($member->id, ['is_owner' => false]);

        expect($travel->members)->toHaveCount(2)
            ->and($travel->isOwner($owner))->toBeTrue()
            ->and($travel->isOwner($member))->toBeFalse();
    });

    it('can check if a user is a member', function () {
        $user = User::factory()->create();
        $travel = Travel::factory()->withOwner($user)->create();
        $travel->members()->attach($user->id);
        $this->actingAs($user);
        expect($travel->isMember())->toBeTrue();
    });

    it('can invite members and accept/refuse invitations', function () {
        Mail::fake();
        $owner = User::factory()->create();
        $travel = Travel::factory()->create();
        $emails = ['invitee1@example.com', 'invitee2@example.com'];
        $travel->inviteMembers($emails, $owner);
        expect($travel->invitations)->toHaveCount(2);

        $invitation = $travel->invitations()->first();
        $user = User::factory()->create();
        $this->actingAs($user);
        $travel->acceptInvitation($invitation->token);
        expect($travel->members()->where('user_id', $user->id)->exists())->toBeTrue();
        expect($travel->invitations()->where('token', $invitation->token)->exists())->toBeFalse();
    });

    it('can refuse an invitation', function () {
        Mail::fake();
        $owner = User::factory()->create();
        $travel = Travel::factory()->create();
        $emails = ['invitee3@example.com'];
        $travel->inviteMembers($emails, $owner);
        $invitation = $travel->invitations()->first();
        $travel->refuseInvitation($invitation->token);
        expect($travel->invitations()->where('token', $invitation->token)->exists())->toBeFalse();
    });

    it('can have activities and housings', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $this->actingAs($owner);
        Activity::factory(3)->create(['travel_id' => $travel->id]);
        Housing::factory(2)->create(['travel_id' => $travel->id]);
        expect($travel->activities)->toHaveCount(3)
            ->and($travel->housings)->toHaveCount(2);
    });

    it('scopePast returns only past travels', function () {
        $past = Travel::factory()->create(['end_date' => now()->subDay()]);
        $future = Travel::factory()->create(['end_date' => now()->addDay()]);

        $result = Travel::past()->get();
        expect($result->pluck('id'))->toContain($past->id)
            ->not->toContain($future->id);
    });

    it('scopeActive returns only active travels', function () {
        $active = Travel::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $inactive = Travel::factory()->create([
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(3),
        ]);
        $result = Travel::active()->get();
        expect($result->pluck('id'))->toContain($active->id)
            ->not->toContain($inactive->id);
    });

    it('scopeUpcoming returns only upcoming travels', function () {
        $upcoming = Travel::factory()->create(['start_date' => now()->addDays(2)]);
        $current = Travel::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $result = Travel::upcoming()->get();
        expect($result->pluck('id'))->toContain($upcoming->id)
            ->not->toContain($current->id);
    });

    it('scopeNoDate returns travels with missing dates', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $noStart = Travel::factory()->create(['start_date' => null]);
        $noEnd = Travel::factory()->create(['end_date' => null]);
        $full = Travel::factory()->create(['start_date' => now(), 'end_date' => now()->addDay()]);

        // Attach the user as a member to all travels
        $noStart->members()->attach($user->id);
        $noEnd->members()->attach($user->id);
        $full->members()->attach($user->id);

        $result = Travel::noDate()->get();
        expect($result->pluck('id'))->toContain($noStart->id)->toContain($noEnd->id)
            ->not->toContain($full->id);
    });

    it('can retrieve the owner of a travel', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $travel->members()->attach($member->id, ['is_owner' => false]);

        expect($travel->owner()->id)->toBe($owner->id);
    });

    it('can find travel from invitation token', function () {
        Mail::fake();
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $travel->inviteMembers(['test@example.com'], $owner);

        $invitation = $travel->invitations()->first();
        $foundTravel = Travel::fromInvitation($invitation->token);

        expect($foundTravel->id)->toBe($travel->id);
    });

    it('returns null for invalid invitation token', function () {
        $foundTravel = Travel::fromInvitation('invalid-token');
        expect($foundTravel)->toBeNull();
    });

    it('applies global scope to filter by user membership', function () {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $memberTravel = Travel::factory()->withOwner($user)->create();
        $nonMemberTravel = Travel::factory()->withOwner($owner)->create();

        $this->actingAs($user);
        $travels = Travel::all();

        expect($travels->pluck('id'))->toContain($memberTravel->id)
            ->not->toContain($nonMemberTravel->id);
    });

    it('can be soft deleted', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $travel->delete();

        expect(Travel::count())->toBe(0)
            ->and(Travel::withTrashed()->count())->toBe(1)
            ->and(Travel::withTrashed()->first()->deleted_at)->not->toBeNull();
    });

    it('can get events for a specific day', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $this->actingAs($owner);

        $today = now();

        // Create an activity for today
        Activity::factory()->create([
            'travel_id' => $travel->id,
            'start_date' => $today,
            'end_date' => $today,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'name' => 'Test Activity',
            'place_name' => 'Test Place',
            'place_latitude' => 1.0,
            'place_longitude' => 1.0,
        ]);

        // Create a housing for today
        Housing::factory()->create([
            'travel_id' => $travel->id,
            'start_date' => $today,
            'end_date' => $today,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'name' => 'Test Housing',
            'place_name' => 'Test Location',
            'place_latitude' => 2.0,
            'place_longitude' => 2.0,
        ]);

        $events = $travel->getDayEvents($today);

        expect($events)->toHaveCount(2)
            ->and($events[0])->toHaveKeys(['name', 'start_time', 'end_time', 'latitude', 'longitude', 'place_name'])
            ->and($events[0]['name'])->toBe('Test Activity')
            ->and($events[1]['name'])->toBe('Test Housing');
    });

    it('returns empty array for day with no events', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $this->actingAs($owner);

        $events = $travel->getDayEvents(now()->addDays(7));
        expect($events)->toBeArray()->toBeEmpty();
    });

    it('skips events with empty dates in getDayEvents', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Créer une activité sans dates
        $activity = Activity::factory()->forTravel($travel)->create([
            'start_date' => null,
            'end_date' => null,
        ]);

        $events = $travel->getDayEvents(Carbon::now());

        expect($events)->toBeArray()
            ->and($events)->toHaveCount(0);
    });

    it('skips events with missing start or end date in getDayEvents', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Créer une activité avec seulement start_date
        $activity1 = Activity::factory()->forTravel($travel)->create([
            'start_date' => Carbon::now(),
            'end_date' => null,
        ]);

        // Créer une housing avec seulement end_date
        $housing1 = Housing::factory()->forTravel($travel)->create([
            'start_date' => null,
            'end_date' => Carbon::now(),
        ]);

        $events = $travel->getDayEvents(Carbon::now());

        // Aucun événement ne devrait être retourné car ils ont des dates manquantes
        expect($events)->toBeArray()
            ->and($events)->toHaveCount(0);
    });

    it('skips events with null dates to avoid Carbon parse error', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Créer une activité avec start_date null et end_date valide
        Activity::factory()->forTravel($travel)->create([
            'start_date' => null,
            'end_date' => Carbon::now(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'name' => 'Test Activity',
            'place_name' => 'Test Place',
            'place_latitude' => 1.0,
            'place_longitude' => 1.0,
        ]);

        // Créer une housing avec start_date valide et end_date null
        Housing::factory()->forTravel($travel)->create([
            'start_date' => Carbon::now(),
            'end_date' => null,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'name' => 'Test Housing',
            'place_name' => 'Test Location',
            'place_latitude' => 2.0,
            'place_longitude' => 2.0,
        ]);

        // Créer une activité avec des dates complètement null
        Activity::factory()->forTravel($travel)->create([
            'start_date' => null,
            'end_date' => null,
            'start_time' => '16:00',
            'end_time' => '17:00',
            'name' => 'Test Activity 2',
            'place_name' => 'Test Place 2',
            'place_latitude' => 3.0,
            'place_longitude' => 3.0,
        ]);

        $events = $travel->getDayEvents(Carbon::now());

        // Aucun événement ne devrait être retourné car les dates sont incomplètes
        // Cette ligne force l'exécution du continue; à la ligne 274
        expect($events)->toBeArray()
            ->and($events)->toHaveCount(0);
    });

    it('forces continue execution with null dates that trigger empty check', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Force l'exécution du continue en créant des activités avec dates null
        // qui seront converties en valeurs vides lors de la récupération
        Activity::factory()->forTravel($travel)->create([
            'start_date' => null,
            'end_date' => null,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'name' => 'Empty Date Activity',
            'place_name' => 'Test Place',
            'place_latitude' => 1.0,
            'place_longitude' => 1.0,
        ]);

        Housing::factory()->forTravel($travel)->create([
            'start_date' => null,
            'end_date' => null,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'name' => 'Empty Date Housing',
            'place_name' => 'Test Location',
            'place_latitude' => 2.0,
            'place_longitude' => 2.0,
        ]);

        $events = $travel->getDayEvents(Carbon::now());

        // Les événements avec dates null doivent déclencher le continue ligne 274
        expect($events)->toBeArray()
            ->and($events)->toHaveCount(0);
    });

    // Tests pour les méthodes d'autorisation
    it('can check user permissions', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($travel->can($member, 'view'))->toBeTrue();
        expect($travel->can($member, 'update'))->toBeTrue();
        expect($travel->can($member, 'delete'))->toBeTrue();
    });

    it('can check view permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($travel->canView($member))->toBeTrue();
    });

    it('can check update permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($travel->canUpdate($member))->toBeTrue();
    });

    it('can check delete permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($travel->canDelete($member))->toBeTrue();
    });

    it('can check invite members permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($travel->canInviteMembers($member))->toBeTrue();
    });

    it('can check manage members permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($travel->canManageMembers($member))->toBeTrue();
    });

    it('non member cannot perform actions', function () {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        expect($travel->can($nonMember, 'view'))->toBeFalse();
        expect($travel->can($nonMember, 'update'))->toBeFalse();
        expect($travel->can($nonMember, 'delete'))->toBeFalse();
        expect($travel->canView($nonMember))->toBeFalse();
        expect($travel->canUpdate($nonMember))->toBeFalse();
        expect($travel->canDelete($nonMember))->toBeFalse();
        expect($travel->canInviteMembers($nonMember))->toBeFalse();
        expect($travel->canManageMembers($nonMember))->toBeFalse();
    });
});
