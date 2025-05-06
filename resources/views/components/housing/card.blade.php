@props(['housing', 'travel'])

<div class="bg-primary-500 rounded-md shadow-md">

    <x-card-image-carrousel :medias="$housing->getMediaDisplay()" />

    <a href="{{ route('travel.housing.show', ['travelId' => $travel->id, 'housingId' => $housing->id]) }}" wire:navigate class="flex flex-col p-4">
        <h2 class="text-lg font-semibold text-zinc-800">
            {{ $housing->name }}
        </h2>

        <p class="text-zinc-600">
            {{ $housing->description }}
        </p>

        <x-show-place-name :placeName="$housing->place_name" />

        <x-show-url :url="$housing->url" />

        <x-show-price :priceByGroup="$housing->price_by_group" :priceByPerson="$housing->price_by_person" />

        <x-show-date :startDate="$housing->start_date" :endDate="$housing->end_date" :startTime="$housing->start_time" :endTime="$housing->end_time" />
    </a>

</div>
