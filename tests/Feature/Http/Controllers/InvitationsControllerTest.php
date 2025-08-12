<?php

use App\Models\Travel;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('authenticated user can accept a valid invitation', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $travel = Travel::factory()->withOwner($inviter)->create();

    // Create invitation
    $travel->inviteMembers([$invitee->email], $inviter);
    $invitation = $travel->invitations()->first();

    $this->actingAs($invitee);

    $response = $this->get(route('travel.invitation.accept', ['token' => $invitation->token]));

    $response->assertRedirect(route('travel.show', $travel->id));

    // Verify user is now a member
    expect($travel->isMember())->toBeTrue();

    // Verify invitation is deleted
    expect($travel->invitations()->where('token', $invitation->token)->exists())->toBeFalse();
});

test('authenticated user can refuse a valid invitation', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $travel = Travel::factory()->withOwner($inviter)->create();

    // Create invitation
    $travel->inviteMembers([$invitee->email], $inviter);
    $invitation = $travel->invitations()->first();

    $this->actingAs($invitee);

    $response = $this->get(route('travel.invitation.refuse', ['token' => $invitation->token]));

    $response->assertRedirect(route('travel.index'));

    // Verify user is not a member
    expect($travel->isMember())->toBeFalse();

    // Verify invitation is deleted
    expect($travel->invitations()->where('token', $invitation->token)->exists())->toBeFalse();
});

test('user cannot accept invitation with invalid token', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('travel.invitation.accept', ['token' => 'invalid-token']));

    $response->assertRedirect(route('travel.index'));
});

test('user cannot refuse invitation with invalid token', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('travel.invitation.refuse', ['token' => 'invalid-token']));

    $response->assertRedirect(route('travel.index'));
});

test('user cannot accept invitation if already a member', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $member = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create();

    // Add user as a member first
    $travel->members()->attach($member->id);

    // Create invitation (this could happen if invitation was sent before they became a member)
    $travel->inviteMembers([$member->email], $owner);
    $invitation = $travel->invitations()->first();

    $this->actingAs($member);

    $response = $this->get(route('travel.invitation.accept', ['token' => $invitation->token]));

    $response->assertRedirect(route('travel.show', $travel->id));
});

test('user cannot refuse invitation if already a member', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $member = User::factory()->create();
    $travel = Travel::factory()->withOwner($owner)->create();

    // Add user as a member first
    $travel->members()->attach($member->id);

    // Create invitation (this could happen if invitation was sent before they became a member)
    $travel->inviteMembers([$member->email], $owner);
    $invitation = $travel->invitations()->first();

    $this->actingAs($member);

    $response = $this->get(route('travel.invitation.refuse', ['token' => $invitation->token]));

    $response->assertRedirect(route('travel.show', $travel->id));
});

test('unauthenticated user cannot accept invitation', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $travel = Travel::factory()->withOwner($inviter)->create();
    $travel->inviteMembers(['test@example.com'], $inviter);
    $invitation = $travel->invitations()->first();

    $response = $this->get(route('travel.invitation.accept', ['token' => $invitation->token]));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot refuse invitation', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $travel = Travel::factory()->withOwner($inviter)->create();
    $travel->inviteMembers(['test@example.com'], $inviter);
    $invitation = $travel->invitations()->first();

    $response = $this->get(route('travel.invitation.refuse', ['token' => $invitation->token]));

    $response->assertRedirect(route('login'));
});

test('accepting invitation with token from deleted invitation fails', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $travel = Travel::factory()->withOwner($inviter)->create();

    // Create and then delete invitation
    $travel->inviteMembers([$invitee->email], $inviter);
    $invitation = $travel->invitations()->first();
    $token = $invitation->token;
    $invitation->delete();

    $this->actingAs($invitee);

    $response = $this->get(route('travel.invitation.accept', ['token' => $token]));

    $response->assertRedirect(route('travel.index'));
});

test('refusing invitation with token from deleted invitation fails', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $travel = Travel::factory()->withOwner($inviter)->create();

    // Create and then delete invitation
    $travel->inviteMembers([$invitee->email], $inviter);
    $invitation = $travel->invitations()->first();
    $token = $invitation->token;
    $invitation->delete();

    $this->actingAs($invitee);

    $response = $this->get(route('travel.invitation.refuse', ['token' => $token]));

    $response->assertRedirect(route('travel.index'));
});

test('accepting invitation works with different user than invitation email', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $travel = Travel::factory()->withOwner($inviter)->create();

    // Create invitation for different email
    $travel->inviteMembers(['different@example.com'], $inviter);
    $invitation = $travel->invitations()->first();

    $this->actingAs($invitee);

    $response = $this->get(route('travel.invitation.accept', ['token' => $invitation->token]));

    $response->assertRedirect(route('travel.show', $travel->id));

    // Verify user is now a member (even though email doesn't match)
    expect($travel->isMember())->toBeTrue();
    expect($travel->invitations()->where('token', $invitation->token)->exists())->toBeFalse();
});

test('refusing invitation works with different user than invitation email', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $travel = Travel::factory()->withOwner($inviter)->create();

    // Create invitation for different email
    $travel->inviteMembers(['different@example.com'], $inviter);
    $invitation = $travel->invitations()->first();

    $this->actingAs($invitee);

    $response = $this->get(route('travel.invitation.refuse', ['token' => $invitation->token]));

    $response->assertRedirect(route('travel.index'));

    // Verify user is not a member and invitation is deleted
    expect($travel->isMember())->toBeFalse();
    expect($travel->invitations()->where('token', $invitation->token)->exists())->toBeFalse();
});
