<?php

namespace App\Livewire\Housing;

use Flux\Flux;
use Carbon\Carbon;
use App\Models\Travel;
use Livewire\Component;
use Carbon\CarbonPeriod;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Validate;

class Create extends Component
{
    use WithFileUploads;


    public Travel $travel;

    #[Validate('required')]
    public $name;
    public $description;
    public array $tempImages = []; // to add files instead of replacing the originals
    public array $images = []; // accepts multiple files
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

    public $availableStartDates;
    public $startDate;
    public $startTime;
    public $availableEndDates;
    public $endDate;
    public $endTime;
    public $travelDateRange;


    public function mount(Travel $travel)
    {
        $this->travel = $travel;

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
        if ($this->startDate == null) {
            $this->endDate = null;
        }

        if ($this->endDate == null || $this->endDate < $this->startDate) {
            $this->endDate = $this->startDate;
        }


        $this->refreshAvailableEndDates($this->startDate);
    }


    public function updatedImages()
    {
        foreach ($this->images as $image) {
            $this->tempImages[] = $image;
        }

        $this->images = [];
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

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        $housing = $this->travel->housings()->create([
            'name' => $this->name,
            'description' => $this->description,
            'place_name' => $this->place['display_name'],
            'place_latitude' => $this->place['lat'],
            'place_longitude' => $this->place['lng'],
            'url' => $this->url,
            'price_by_person' => $this->priceType == 'price_by_person' ? $this->price : null,
            'price_by_group' => $this->priceType == 'price_by_group' ? $this->price : null,
            'start_date' => $this->startDate,
            'start_time' => $this->startTime,
            'end_date' => $this->endDate,
            'end_time' => $this->endTime,
        ]);

        foreach ($this->tempImages as $image) {
            $housing
                ->addMedia($image->getRealPath())
                ->usingName($image->getClientOriginalName())
                ->toMediaCollection();
        }

        $this->cleanupFields();

        $this->dispatch('housingCreated');
        Flux::modals()->close();
        Toaster::success('Logement créé!');
    }

    public function cleanupFields()
    {
        $this->name = null;
        $this->description = null;
        $this->place = [
            'display_name' => null,
            'lat' => null,
            'lng' => null,
        ];
        $this->url = null;
        $this->tempImages = [];
        $this->images = [];
        $this->priceType = 'price_by_person';
        $this->price = null;

        $this->startDate = null;
        $this->startTime = null;
        $this->endDate = null;
        $this->endTime = null;
        $this->refreshAvailableEndDates(Carbon::parse($this->travel->start_date));

        $this->dispatch(event: 'clean-map');
    }
    public function render()
    {
        return view('livewire.housing.create');
    }
}
