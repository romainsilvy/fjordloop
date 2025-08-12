<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
        $response = $this->get('/reset-password/' . $notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
        $response = Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('resetPassword');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });
});

test('password reset fails with invalid token', function () {
    $user = User::factory()->create();

    $response = Livewire::test(ResetPassword::class, ['token' => 'invalid-token'])
        ->set('email', $user->email)
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('resetPassword');

    $response->assertHasErrors(['email']);
});

test('password reset fails with invalid email', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
        $response = Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', 'wrong@example.com')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('resetPassword');

        $response->assertHasErrors(['email']);

        return true;
    });
});

test('password reset validates required fields', function () {
    $response = Livewire::test(ResetPassword::class, ['token' => 'some-token'])
        ->set('email', '')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->call('resetPassword');

    $response->assertHasErrors(['email', 'password']);
});

test('password reset validates email format', function () {
    $response = Livewire::test(ResetPassword::class, ['token' => 'some-token'])
        ->set('email', 'invalid-email')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('resetPassword');

    $response->assertHasErrors(['email']);
});

test('password reset validates password confirmation', function () {
    $response = Livewire::test(ResetPassword::class, ['token' => 'some-token'])
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'different-password')
        ->call('resetPassword');

    $response->assertHasErrors(['password']);
});

test('password reset validates password strength', function () {
    $response = Livewire::test(ResetPassword::class, ['token' => 'some-token'])
        ->set('email', 'test@example.com')
        ->set('password', '123')
        ->set('password_confirmation', '123')
        ->call('resetPassword');

    $response->assertHasErrors(['password']);
});

test('component can handle email parameter from request', function () {
    $token = 'test-token';
    $email = 'test@example.com';

    // Test by making an actual HTTP request with email parameter
    $response = $this->get("/reset-password/{$token}?email=" . urlencode($email));

    $response->assertStatus(200);
    // The component should render successfully with the email parameter
    $response->assertSee('reset-password'); // Check that the reset form is present
});

test('component mount method sets token and email correctly', function () {
    $token = 'test-token';

    // We test the initialization logic by creating the component
    $component = Livewire::test(ResetPassword::class, ['token' => $token]);

    expect($component->get('token'))->toBe($token);
    // Email will be empty unless it's in the request
    expect($component->get('password'))->toBe('');
    expect($component->get('password_confirmation'))->toBe('');
});

test('component initializes with empty email when not in request', function () {
    $token = 'test-token';

    // Clear any existing request parameters
    request()->replace([]);

    $component = Livewire::test(ResetPassword::class, ['token' => $token]);

    expect($component->get('token'))->toBe($token);
    expect($component->get('email'))->toBe('');
});

test('password reset dispatches password reset event', function () {
    Event::fake();
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
        Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('resetPassword');

        Event::assertDispatched(\Illuminate\Auth\Events\PasswordReset::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        return true;
    });
});

test('successful password reset updates user password and remember token', function () {
    Notification::fake();

    $user = User::factory()->create();
    $originalPassword = $user->password;
    $originalRememberToken = $user->remember_token;

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user, $originalPassword, $originalRememberToken) {
        Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'new-secure-password')
            ->set('password_confirmation', 'new-secure-password')
            ->call('resetPassword');

        $user->refresh();

        // Password should be updated
        expect($user->password)->not->toBe($originalPassword);
        expect(Hash::check('new-secure-password', $user->password))->toBeTrue();

        // Remember token should be regenerated
        expect($user->remember_token)->not->toBe($originalRememberToken);
        expect($user->remember_token)->toHaveLength(60);

        return true;
    });
});
