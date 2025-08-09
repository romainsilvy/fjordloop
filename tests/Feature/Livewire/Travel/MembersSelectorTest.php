<?php

use App\Livewire\Travel\MembersSelector;
use Livewire\Livewire;

test('members selector component can be rendered', function () {
    Livewire::test(MembersSelector::class)
        ->assertStatus(200);
});

test('component initializes with empty data', function () {
    $component = Livewire::test(MembersSelector::class);

    expect($component->get('members'))->toBe([]);
    expect($component->get('error'))->toBe('');
    expect($component->get('memberToAdd'))->toBe('');
    expect($component->get('title'))->toBe('Membres');
});

test('can add valid email member', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'test@example.com')
        ->call('addMember');

    expect($component->get('members'))->toBe(['test@example.com']);
    expect($component->get('memberToAdd'))->toBe('');
    expect($component->get('error'))->toBe('');
});

test('can add multiple members', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'user1@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user2@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user3@example.com')
        ->call('addMember');

    expect($component->get('members'))->toBe([
        'user1@example.com',
        'user2@example.com',
        'user3@example.com'
    ]);
    expect($component->get('memberToAdd'))->toBe('');
    expect($component->get('error'))->toBe('');
});

test('validates email format when adding member', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'invalid-email')
        ->call('addMember');

    expect($component->get('members'))->toBe([]);
    expect($component->get('memberToAdd'))->toBe('invalid-email');
    expect($component->get('error'))->toContain('email');
});

test('validates required email when adding member', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', '')
        ->call('addMember');

    expect($component->get('members'))->toBe([]);
    expect($component->get('memberToAdd'))->toBe('');
    expect($component->get('error'))->toContain('obligatoire'); // French validation message
});

test('prevents duplicate members', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'test@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'test@example.com')
        ->call('addMember');

    expect($component->get('members'))->toBe(['test@example.com']);
    expect($component->get('memberToAdd'))->toBe('test@example.com'); // Not cleared for duplicates
    expect($component->get('error'))->toBe('');
});

test('clears error when adding valid member after error', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'invalid-email')
        ->call('addMember') // This will set an error
        ->set('memberToAdd', 'valid@example.com')
        ->call('addMember'); // This should clear the error

    expect($component->get('members'))->toBe(['valid@example.com']);
    expect($component->get('error'))->toBe('');
});

test('can delete member by index', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'user1@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user2@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user3@example.com')
        ->call('addMember')
        ->call('deleteMember', 1); // Delete middle member

    $members = $component->get('members');
    expect($members)->toHaveCount(2);
    expect($members[0])->toBe('user1@example.com');
    expect($members[2])->toBe('user3@example.com'); // Index 2 remains
    expect(isset($members[1]))->toBeFalse(); // Index 1 was unset
});

test('can delete first member', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'user1@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user2@example.com')
        ->call('addMember')
        ->call('deleteMember', 0);

    $members = $component->get('members');
    expect($members)->toHaveCount(1);
    expect($members[1])->toBe('user2@example.com');
    expect(isset($members[0]))->toBeFalse();
});

test('can delete last member', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'user1@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user2@example.com')
        ->call('addMember')
        ->call('deleteMember', 1);

    $members = $component->get('members');
    expect($members)->toHaveCount(1);
    expect($members[0])->toBe('user1@example.com');
    expect(isset($members[1]))->toBeFalse();
});

test('deleting invalid index does not crash', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'user1@example.com')
        ->call('addMember')
        ->call('deleteMember', 999); // Invalid index

    expect($component->get('members'))->toBe(['user1@example.com']);
});

test('cleanup resets all data', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'test@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'another@example.com')
        ->set('error', 'Some error')
        ->call('cleanUp');

    expect($component->get('members'))->toBe([]);
    expect($component->get('memberToAdd'))->toBe('');
    expect($component->get('error'))->toBe('');
});

test('cleanup responds to clean-members event', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'test@example.com')
        ->call('addMember')
        ->dispatch('clean-members');

    expect($component->get('members'))->toBe([]);
    expect($component->get('memberToAdd'))->toBe('');
    expect($component->get('error'))->toBe('');
});

test('can customize title', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('title', 'Custom Title');

    expect($component->get('title'))->toBe('Custom Title');
});

test('validates various email formats', function () {
    $validEmails = [
        'test@example.com',
        'user.name@domain.co.uk',
        'user+tag@example.org',
        'user_name@example-domain.com',
        '123@domain.com',
    ];

    $component = Livewire::test(MembersSelector::class);

    foreach ($validEmails as $email) {
        $component
            ->set('memberToAdd', $email)
            ->call('addMember');

        expect($component->get('error'))->toBe('', "Email {$email} should be valid");
    }

    expect($component->get('members'))->toHaveCount(count($validEmails));
});

test('rejects invalid email formats', function () {
    $invalidEmails = [
        'invalid-email',
        '@domain.com',
        'user@',
        'user..name@domain.com',
        'user name@domain.com',
    ];

    foreach ($invalidEmails as $email) {
        $component = Livewire::test(MembersSelector::class)
            ->set('memberToAdd', $email)
            ->call('addMember');

        expect($component->get('error'))->not->toBe('', "Email {$email} should be invalid");
        expect($component->get('members'))->toBeEmpty("No members should be added for invalid email {$email}");
    }
});

test('handles edge cases for member deletion', function () {
    $component = Livewire::test(MembersSelector::class);

    // Try to delete from empty array
    $component->call('deleteMember', 0);
    expect($component->get('members'))->toBe([]);

    // Add members and delete all
    $component
        ->set('memberToAdd', 'user1@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user2@example.com')
        ->call('addMember')
        ->call('deleteMember', 0)
        ->call('deleteMember', 1);

    expect($component->get('members'))->toBeEmpty();
});

test('preserves array structure after deletions', function () {
    $component = Livewire::test(MembersSelector::class)
        ->set('memberToAdd', 'user1@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user2@example.com')
        ->call('addMember')
        ->set('memberToAdd', 'user3@example.com')
        ->call('addMember')
        ->call('deleteMember', 1); // Delete middle member

    $members = $component->get('members');

    // Check that indices are preserved (array is not re-indexed)
    expect(array_key_exists(0, $members))->toBeTrue();
    expect(array_key_exists(1, $members))->toBeFalse();
    expect(array_key_exists(2, $members))->toBeTrue();

    expect($members[0])->toBe('user1@example.com');
    expect($members[2])->toBe('user3@example.com');
});
