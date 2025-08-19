<?php

use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('Housing model', function () {
    beforeEach(function () {
        Storage::fake('s3');
    });

    it('can be created with fillable fields', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $housing = Housing::factory()->create([
            'travel_id' => $travel->id,
            'name' => 'Test Housing',
            'description' => 'Test Description',
            'url' => 'https://example.com',
            'price_by_person' => 50.00,
            'price_by_group' => null,
            'place_name' => 'Test Place',
            'place_latitude' => 48.8566,
            'place_longitude' => 2.3522,
            'start_date' => '2024-01-01',
            'start_time' => '14:00',
            'end_date' => '2024-01-05',
            'end_time' => '11:00',
        ]);

        expect($housing->name)->toBe('Test Housing')
            ->and($housing->description)->toBe('Test Description')
            ->and($housing->url)->toBe('https://example.com')
            ->and($housing->price_by_person)->toBe(50.00)
            ->and($housing->price_by_group)->toBeNull()
            ->and($housing->place_name)->toBe('Test Place')
            ->and($housing->place_latitude)->toBe(48.8566)
            ->and($housing->place_longitude)->toBe(2.3522)
            ->and($housing->start_date->toDateString())->toBe('2024-01-01')
            ->and($housing->start_time)->toBe('14:00')
            ->and($housing->end_date->toDateString())->toBe('2024-01-05')
            ->and($housing->end_time)->toBe('11:00');
    });

    it('formats time attributes correctly', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $housing = Housing::factory()->create([
            'travel_id' => $travel->id,
            'start_time' => '09:30:00',
            'end_time' => '17:45:00',
        ]);

        expect($housing->start_time)->toBe('09:30')
            ->and($housing->end_time)->toBe('17:45');
    });

    it('handles null time attributes', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $housing = Housing::factory()->create([
            'travel_id' => $travel->id,
            'start_time' => null,
            'end_time' => null,
        ]);

        expect($housing->start_time)->toBeNull()
            ->and($housing->end_time)->toBeNull();
    });

    it('belongs to a travel', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->create(['travel_id' => $travel->id]);

        expect($housing->travel)->toBeInstanceOf(Travel::class)
            ->and($housing->travel->id)->toBe($travel->id);
    });

    it('applies global scope to filter by user membership', function () {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->create(['travel_id' => $travel->id]);

        $this->actingAs($owner);
        expect(Housing::count())->toBe(1);

        $this->actingAs($nonMember);
        expect(Housing::count())->toBe(0);
    });

    it('applies global scope when no user is authenticated', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->create(['travel_id' => $travel->id]);

        // No user authenticated
        $this->app['auth']->forgetGuards();
        expect(Housing::count())->toBe(0);
    });

    it('can handle media attachments', function () {
        Storage::fake('public');

        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->create(['travel_id' => $travel->id]);

        $file = UploadedFile::fake()->image('housing.jpg');
        $housing->addMedia($file->getRealPath())
            ->usingName('housing-image.jpg')
            ->toMediaCollection();

        // Test basic media functionality
        expect($housing->getMedia())->toHaveCount(1);
        expect($housing->getMedia()->first()->name)->toBe('housing-image.jpg');

        // Mock getTemporaryUrl to test getMediaDisplay without S3 calls
        $originalMedia = $housing->getMedia()->first();
        $mockedMedia = Mockery::mock($originalMedia)->makePartial();
        $mockedMedia->shouldReceive('getTemporaryUrl')
            ->andReturn('http://localhost/storage/mocked-temp-url.jpg');

        // Replace the media in the collection with our mock
        $housing->setRelation('media', collect([$mockedMedia]));

        // Now test getMediaDisplay
        $mediaDisplay = $housing->getMediaDisplay();
        expect($mediaDisplay)->toBeCollection()
            ->and($mediaDisplay->first())->toHaveKeys(['id', 'url', 'name'])
            ->and($mediaDisplay->first()['name'])->toBeString() // file_name is auto-generated
            ->and($mediaDisplay->first()['url'])->toBe('http://localhost/storage/mocked-temp-url.jpg');
    });

    it('can filter housings with place information', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $this->actingAs($owner);

        $withPlace = Housing::factory()->create([
            'travel_id' => $travel->id,
            'place_name' => 'Test Place',
            'place_latitude' => 48.8566,
            'place_longitude' => 2.3522,
        ]);

        $withoutPlace = Housing::factory()->create([
            'travel_id' => $travel->id,
            'place_name' => null,
            'place_latitude' => null,
            'place_longitude' => null,
        ]);

        $housingsWithPlace = Housing::hasPlace()->get();
        expect($housingsWithPlace)->toHaveCount(1)
            ->and($housingsWithPlace->first()->id)->toBe($withPlace->id);
    });

    it('can be created with group price instead of person price', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $housing = Housing::factory()->create([
            'travel_id' => $travel->id,
            'price_by_person' => null,
            'price_by_group' => 200.00,
        ]);

        expect($housing->price_by_person)->toBeNull()
            ->and($housing->price_by_group)->toBe(200.00);
    });

    it('uses UUIDs as primary keys', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->create(['travel_id' => $travel->id]);

        expect($housing->id)->toBeString()
            ->and(strlen($housing->id))->toBe(36);
    });

    // Tests pour les méthodes d'autorisation
    it('can check user permissions', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($housing->can($member, 'view'))->toBeTrue();
        expect($housing->can($member, 'update'))->toBeTrue();
        expect($housing->can($member, 'delete'))->toBeTrue();
    });

    it('can check view permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($housing->canView($member))->toBeTrue();
    });

    it('can check update permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($housing->canUpdate($member))->toBeTrue();
    });

    it('can check delete permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($housing->canDelete($member))->toBeTrue();
    });

    it('can check create for travel permission', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        expect($housing->canCreateForTravel($member))->toBeTrue();
    });

    it('non member cannot perform actions', function () {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        expect($housing->can($nonMember, 'view'))->toBeFalse();
        expect($housing->can($nonMember, 'update'))->toBeFalse();
        expect($housing->can($nonMember, 'delete'))->toBeFalse();
        expect($housing->canView($nonMember))->toBeFalse();
        expect($housing->canUpdate($nonMember))->toBeFalse();
        expect($housing->canDelete($nonMember))->toBeFalse();
        expect($housing->canCreateForTravel($nonMember))->toBeFalse();
    });
});
