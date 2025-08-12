<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login');

    $response->assertHasErrors('email');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');

    $this->assertGuest();
});

test('login validates required email field', function () {
    $response = Livewire::test(Login::class)
        ->set('email', '')
        ->set('password', 'password')
        ->call('login');

    $response->assertHasErrors(['email' => 'required']);
});

test('login validates email format', function () {
    $response = Livewire::test(Login::class)
        ->set('email', 'invalid-email')
        ->set('password', 'password')
        ->call('login');

    $response->assertHasErrors(['email' => 'email']);
});

test('login validates required password field', function () {
    $response = Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', '')
        ->call('login');

    $response->assertHasErrors(['password' => 'required']);
});

test('users can login with remember me option', function () {
    $user = User::factory()->create();

    $response = Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->set('remember', true)
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('failed login attempts are rate limited', function () {
    $user = User::factory()->create();

    // Make 5 failed attempts to trigger rate limiting
    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login');
    }

    // The 6th attempt should be rate limited
    $response = Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login');

    $response->assertHasErrors(['email']);

    // The error message should indicate throttling
    $errors = $response->errors();
    expect($errors->get('email')[0])->toContain('Trop de tentatives');
});

test('rate limiting lockout event is dispatched', function () {
    Event::fake();

    $user = User::factory()->create();

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login');
    }

    // The 6th attempt should trigger lockout event
    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login');

    Event::assertDispatched(\Illuminate\Auth\Events\Lockout::class);
});

test('successful login clears rate limiter', function () {
    $user = User::factory()->create();

    // Make some failed attempts first
    for ($i = 0; $i < 3; $i++) {
        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login');
    }

    // Successful login should clear the rate limiter
    $response = Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    $response->assertHasNoErrors();
    $this->assertAuthenticated();

    // After successful login, failed attempts should start fresh
    Auth::logout();

    $response = Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login');

    $response->assertHasErrors(['email']);
    // Should get the standard auth failed message, not throttled message
    $errors = $response->errors();
    expect($errors->get('email')[0])->not->toContain('Trop de tentatives');
});

test('component initializes with empty fields', function () {
    $component = Livewire::test(Login::class);

    expect($component->get('email'))->toBe('');
    expect($component->get('password'))->toBe('');
    expect($component->get('remember'))->toBe(false);
});

test('throttle key is generated correctly', function () {
    $component = Livewire::test(Login::class)
        ->set('email', 'Test@Example.COM')
        ->set('password', 'password');

    // We can't directly test the throttleKey method since it's protected,
    // but we can verify the rate limiting works with email case variations
    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('email', 'test@example.com') // lowercase
            ->set('password', 'wrong-password')
            ->call('login');
    }

    // Using different case should still be rate limited (same throttle key)
    $response = Livewire::test(Login::class)
        ->set('email', 'TEST@EXAMPLE.COM') // uppercase
        ->set('password', 'wrong-password')
        ->call('login');

    $response->assertHasErrors(['email']);
    $errors = $response->errors();
    expect($errors->get('email')[0])->toContain('Trop de tentatives');
});
