<?php

namespace App\Livewire\Activity;

use Flux\Flux;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\Activity;
use Carbon\CarbonPeriod;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isNull;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Update extends Component
{
    use WithFileUploads;

    public ?Activity $activity;
    public $existingMedia = [];
    public $mediaToDelete = [];
    #[Validate(['images.*' => 'image|max:10240'])] // Only images, 10MB max
    public $images = [];
    public $tempImages = []; // to add files instead of replacing the originals

    #[Validate('required')]
    public $name;
    public $description;

    public $place = [
        'display_name' => null,
        'lat' => null,
        'lng' => null,
    ];

    #[Validate('url|nullable')]
    public $url;

    public $availablePrices = [
        'price_by_person' => 'par personne',
        'price_by_group' => 'pour le groupe',
    ];

    #[Validate('numeric|nullable')]
    public $price;
    public $priceType = 'price_by_person';


    public $availableStartDates = [];
    public $startDate;
    public $startTime;
    public $availableEndDates = [];
    public $endDate;
    public $endTime;
    public $travelDateRange;


    public function mount($activity)
    {
        $this->activity = $activity;

        if ($this->activity) {
            $this->initFields();

            Flux::modal('update-activity')->show();

            $this->dispatch(
                'open-map',
                lat: $this->place['lat'],
                lon: $this->place['lng'],
                name: $this->place['display_name'],
            );
        }
    }

    public function initFields()
    {
        $travel = $this->activity->travel;

        $startDate = Carbon::parse($travel->start_date);
        $endDate = Carbon::parse($travel->end_date);

        $datesArray = [];

        $period = CarbonPeriod::create($startDate, $endDate);


        foreach ($period as $date) {
            $key = $date->format('Y-m-d'); // e.g. 2025-06-01
            $value = $date->translatedFormat('l j F'); // e.g. Monday 6 June (with localization support)
            $datesArray[$key] = $value;
        }

        $this->travelDateRange = $datesArray;

        $this->availableStartDates = $this->travelDateRange;
        $this->refreshAvailableEndDates($this->startDate);


        if ($this->activity->price_by_person) {
            $this->priceType = 'price_by_person';
        } elseif ($this->activity->price_by_group) {
            $this->priceType = 'price_by_group';
        } else {
            $this->priceType = 'price_by_person';
        }

        $this->name = $this->activity->name;
        $this->description = $this->activity->description;
        $this->place['display_name'] = $this->activity->place_name;
        $this->place['lat'] = $this->activity->place_latitude;
        $this->place['lng'] = $this->activity->place_longitude;
        $this->url = $this->activity->url;
        $this->price = $this->activity->{$this->priceType};
        $this->startDate = $this->activity->start_date?->format('Y-m-d');
        $this->startTime = $this->activity->start_time;
        $this->endDate = $this->activity->end_date?->format('Y-m-d');
        $this->endTime = $this->activity->end_time;

        $this->loadExistingMedia();
    }


    public function refreshAvailableEndDates($startDate)
    {
        $noDate = ['' => 'pas de date'];
        $availableDates = array_filter($this->travelDateRange, function ($key) use ($startDate) {
            return $key >= $startDate;
        }, ARRAY_FILTER_USE_KEY);

        $this->availableEndDates = $noDate + $availableDates;
    }

    public function updatedStartDate()
    {
        $this->refreshAvailableEndDates($this->startDate);


        if ($this->startDate == null) {
            $this->endDate = null;
        }

        if ($this->endDate == null || $this->endDate < $this->startDate) {
            $this->endDate = $this->startDate;
        }
    }

    public function updatedImages()
    {
        // Make sure we're working with valid image objects
        if (empty($this->images)) {
            return;
        }

        // Add new images to tempImages
        foreach ($this->images as $image) {
            if ($image && $image->isValid()) {
                $this->tempImages[] = $image;
            }
        }

        // Reset images property
        $this->images = [];
    }

    protected function loadExistingMedia()
    {
        if ($this->activity) {
            $this->existingMedia = $this->activity->getMedia()
                ->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->name,
                        'url' => $media->getTemporaryUrl(Carbon::now()->addMinutes(5)),
                        'file_name' => $media->file_name,
                        'marked_for_deletion' => in_array($media->id, $this->mediaToDelete),
                    ];
                })
                ->toArray();
        }
    }

    public function removeImage(int $index)
    {
        if (isset($this->tempImages[$index])) {
            $updatedImages = [];

            foreach ($this->tempImages as $i => $image) {
                if ($i !== $index) {
                    $updatedImages[] = $image;
                }
            }

            $this->tempImages = $updatedImages;
        }
    }

    public function markMediaForDeletion($mediaId)
    {
        if ($this->activity) {
            if (!in_array($mediaId, $this->mediaToDelete)) {
                $this->mediaToDelete[] = $mediaId;
            }

            $this->loadExistingMedia();
        }
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        $this->activity->update([
            'name' => $this->name,
            'description' => $this->description,
            'place_name' => $this->place['display_name'],
            'place_latitude' => $this->place['lat'],
            'place_longitude' => $this->place['lng'],
            'url' => $this->url,
            'price_by_person' => $this->priceType == 'price_by_person' ? $this->price : null,
            'price_by_group' => $this->priceType == 'price_by_group' ? $this->price : null,
            'start_date' => $this->startDate ? Carbon::parse($this->startDate) : null,
            'start_time' => $this->startTime,
            'end_date' => $this->endDate ? Carbon::parse($this->endDate) : null,
            'end_time' => $this->endTime,
        ]);

        // Process only valid temporary images
        foreach ($this->tempImages as $image) {
            if ($image && method_exists($image, 'getRealPath') && file_exists($image->getRealPath())) {
                $originalName = method_exists($image, 'getClientOriginalName') ? $image->getClientOriginalName() : ('image-' . uniqid());

                $this->activity
                    ->addMedia($image->getRealPath())
                    ->usingName($originalName)
                    ->toMediaCollection();
            }
        }

        // Delete marked media
        foreach ($this->mediaToDelete as $mediaId) {
            $this->activity->deleteMedia($mediaId);
        };

        // Reset form
        $this->tempImages = [];
        $this->cleanupFields();

        Flux::modals()->close();
        Toaster::success('Activité modifiée !');

        $this->dispatch(
            'open-map',
            lat: $this->place['lat'],
            lon: $this->place['lng'],
            name: $this->place['display_name'],
        );
        $this->dispatch('activity-updated');
    }

    public function delete()
    {
        $this->activity->delete();
        Toaster::success('Activité supprimée !');
        $this->redirectRoute('travel.show', $this->activity->travel_id, navigate: true);
    }

    public function cleanupFields()
    {
        // Reset temporary images when cleaning up
        $this->tempImages = [];
        $this->images = [];
        $this->mediaToDelete = [];

        $this->initFields();
    }

    public function render()
    {
        return view('livewire.activity.update');
    }
}
