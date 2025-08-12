<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false) . '?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('already verified user redirects to dashboard with verified flag', function () {
    $user = User::factory()->create(); // Creates a verified user by default

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertRedirect(route('dashboard', absolute: false) . '?verified=1');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('verified event is not dispatched for already verified user', function () {
    Event::fake();

    $user = User::factory()->create(); // Creates a verified user by default

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl);

    Event::assertNotDispatched(Verified::class);
});

test('unauthenticated user cannot verify email', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->get($verificationUrl);

    $response->assertRedirect(route('login', absolute: false));
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('expired verification link is rejected', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinutes(60), // Expired URL
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertStatus(403); // Forbidden due to expired signature
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification link with invalid signature is rejected', function () {
    $user = User::factory()->unverified()->create();

    // Create a URL without proper signing
    $verificationUrl = route('verification.verify', [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertStatus(403); // Forbidden due to invalid signature
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification with wrong user id is rejected', function () {
    $user = User::factory()->unverified()->create();
    $otherUser = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $otherUser->id, 'hash' => sha1($user->email)] // Wrong ID
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertStatus(403); // Forbidden due to mismatched user
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
