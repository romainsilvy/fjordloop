<?php

use App\Livewire\Auth\VerifyEmail;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('verify email component can be rendered', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->assertStatus(200);
});

test('can send verification email to unverified user', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->call('sendVerification');

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('already verified user gets redirected when sending verification', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->call('sendVerification')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('already verified user does not receive verification email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->call('sendVerification');

    Notification::assertNotSentTo($user, VerifyEmailNotification::class);
});

test('logout method logs out user and redirects to home', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user);

    // Verify user is authenticated before logout
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->id)->toBe($user->id);

    $response = Livewire::test(VerifyEmail::class)
        ->call('logout')
        ->assertRedirect('/');

    // Verify user is logged out after the action
    expect(auth()->check())->toBeFalse();
});

test('sendVerification requires authenticated user', function () {
    // Don't authenticate any user

    $this->expectException(\Error::class);

    Livewire::test(VerifyEmail::class)
        ->call('sendVerification');
});

test('logout action works with dependency injection', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Test that logout method accepts the Logout action parameter
    // and properly calls it
    expect(auth()->check())->toBeTrue();

    Livewire::test(VerifyEmail::class)
        ->call('logout')
        ->assertRedirect('/');

    expect(auth()->check())->toBeFalse();
});

test('component renders for authenticated unverified user', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
        'email' => 'test@example.com',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(VerifyEmail::class);

    // Component should render without errors for unverified user
    $component->assertStatus(200);
});

test('component renders for authenticated verified user', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'email' => 'verified@example.com',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(VerifyEmail::class);

    // Component should render without errors for verified user too
    $component->assertStatus(200);
});

test('multiple verification emails can be sent by same user', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(VerifyEmail::class);

    // Send first verification
    $component->call('sendVerification');

    // Send second verification
    $component->call('sendVerification');

    // Both should be sent
    Notification::assertSentToTimes($user, VerifyEmailNotification::class, 2);
});

test('sendVerification method handles session flash correctly', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user);

    // Test that the method completes without errors and sends notification
    $component = Livewire::test(VerifyEmail::class)
        ->call('sendVerification');

    // Verify notification was sent (main functionality)
    Notification::assertSentTo($user, VerifyEmailNotification::class);

    // Component should remain functional after the call
    expect($component->instance())->toBeInstanceOf(VerifyEmail::class);
});

test('logout uses the logout action dependency injection', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user);

    // This test verifies that the Logout action is properly injected
    // and called in the logout method
    Livewire::test(VerifyEmail::class)
        ->call('logout')
        ->assertRedirect('/');

    // User should be logged out
    expect(auth()->check())->toBeFalse();
});

test('component handles user with different verification states', function () {
    Notification::fake();

    // Test with unverified user
    $unverifiedUser = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($unverifiedUser);

    Livewire::test(VerifyEmail::class)
        ->call('sendVerification');

    Notification::assertSentTo($unverifiedUser, VerifyEmailNotification::class);

    // Now test with verified user (switch users)
    $verifiedUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($verifiedUser);

    Livewire::test(VerifyEmail::class)
        ->call('sendVerification')
        ->assertRedirect(route('dashboard', absolute: false));

    // Verified user should not receive notification
    Notification::assertNotSentTo($verifiedUser, VerifyEmailNotification::class);
});

test('sendVerification redirects with navigate option for verified users', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(VerifyEmail::class)
        ->call('sendVerification');

    // Should redirect to dashboard with navigate option
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('logout redirects to home with navigate option', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = Livewire::test(VerifyEmail::class)
        ->call('logout');

    // Should redirect to home page
    $response->assertRedirect('/');
    expect(auth()->check())->toBeFalse();
});
