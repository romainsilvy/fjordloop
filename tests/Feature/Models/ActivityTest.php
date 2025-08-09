<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Travel;
use App\Models\Activity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Activity model', function () {
    beforeEach(function () {
        Storage::fake('s3');
    });

    it('can be created with fillable fields', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $activity = Activity::factory()->create([
            'travel_id' => $travel->id,
            'name' => 'Test Activity',
            'description' => 'Test Description',
            'url' => 'https://example.com',
            'price_by_person' => 50.00,
            'price_by_group' => null,
            'place_name' => 'Test Place',
            'place_latitude' => 48.8566,
            'place_longitude' => 2.3522,
            'start_date' => '2024-01-01',
            'start_time' => '09:00',
            'end_date' => '2024-01-01',
            'end_time' => '17:00',
        ]);

        expect($activity->name)->toBe('Test Activity')
            ->and($activity->description)->toBe('Test Description')
            ->and($activity->url)->toBe('https://example.com')
            ->and($activity->price_by_person)->toBe(50.00)
            ->and($activity->price_by_group)->toBeNull()
            ->and($activity->place_name)->toBe('Test Place')
            ->and($activity->place_latitude)->toBe(48.8566)
            ->and($activity->place_longitude)->toBe(2.3522)
            ->and($activity->start_date->toDateString())->toBe('2024-01-01')
            ->and($activity->start_time)->toBe('09:00')
            ->and($activity->end_date->toDateString())->toBe('2024-01-01')
            ->and($activity->end_time)->toBe('17:00');
    });

    it('formats time attributes correctly', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $activity = Activity::factory()->create([
            'travel_id' => $travel->id,
            'start_time' => '09:30:00',
            'end_time' => '17:45:00',
        ]);

        expect($activity->start_time)->toBe('09:30')
            ->and($activity->end_time)->toBe('17:45');
    });

    it('handles null time attributes', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $activity = Activity::factory()->create([
            'travel_id' => $travel->id,
            'start_time' => null,
            'end_time' => null,
        ]);

        expect($activity->start_time)->toBeNull()
            ->and($activity->end_time)->toBeNull();
    });

    it('belongs to a travel', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->create(['travel_id' => $travel->id]);

        expect($activity->travel)->toBeInstanceOf(Travel::class)
            ->and($activity->travel->id)->toBe($travel->id);
    });

    it('applies global scope to filter by user membership', function () {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->create(['travel_id' => $travel->id]);

        $this->actingAs($owner);
        expect(Activity::count())->toBe(1);

        $this->actingAs($nonMember);
        expect(Activity::count())->toBe(0);
    });

    it('applies global scope when no user is authenticated', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->create(['travel_id' => $travel->id]);

        // No user authenticated
        $this->app['auth']->forgetGuards();
        expect(Activity::count())->toBe(0);
    });

    it('can handle media attachments', function () {
        Storage::fake('public');

        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->create(['travel_id' => $travel->id]);

        $file = UploadedFile::fake()->image('activity.jpg');
        $activity->addMedia($file->getRealPath())
            ->usingName('activity-image.jpg')
            ->toMediaCollection();

        // Test basic media functionality
        expect($activity->getMedia())->toHaveCount(1);
        expect($activity->getMedia()->first()->name)->toBe('activity-image.jpg');

        // Mock getTemporaryUrl to test getMediaDisplay without S3 calls
        $originalMedia = $activity->getMedia()->first();
        $mockedMedia = \Mockery::mock($originalMedia)->makePartial();
        $mockedMedia->shouldReceive('getTemporaryUrl')
            ->andReturn('http://localhost/storage/mocked-temp-url.jpg');

        // Replace the media in the collection with our mock
        $activity->setRelation('media', collect([$mockedMedia]));

        // Now test getMediaDisplay
        $mediaDisplay = $activity->getMediaDisplay();
        expect($mediaDisplay)->toBeCollection()
            ->and($mediaDisplay->first())->toHaveKeys(['id', 'url', 'name'])
            ->and($mediaDisplay->first()['name'])->toBeString() // file_name is auto-generated
            ->and($mediaDisplay->first()['url'])->toBe('http://localhost/storage/mocked-temp-url.jpg');
    });

    it('can filter activities with place information', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $this->actingAs($owner);

        $withPlace = Activity::factory()->create([
            'travel_id' => $travel->id,
            'place_name' => 'Test Place',
            'place_latitude' => 48.8566,
            'place_longitude' => 2.3522,
        ]);

        $withoutPlace = Activity::factory()->create([
            'travel_id' => $travel->id,
            'place_name' => null,
            'place_latitude' => null,
            'place_longitude' => null,
        ]);

        $activitiesWithPlace = Activity::hasPlace()->get();
        expect($activitiesWithPlace)->toHaveCount(1)
            ->and($activitiesWithPlace->first()->id)->toBe($withPlace->id);
    });

    it('can be created with group price instead of person price', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $activity = Activity::factory()->create([
            'travel_id' => $travel->id,
            'price_by_person' => null,
            'price_by_group' => 200.00,
        ]);

        expect($activity->price_by_person)->toBeNull()
            ->and($activity->price_by_group)->toBe(200.00);
    });

    it('uses UUIDs as primary keys', function () {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->create(['travel_id' => $travel->id]);

        expect($activity->id)->toBeString()
            ->and(strlen($activity->id))->toBe(36);
    });
});
