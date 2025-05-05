@props(['activity', 'travel'])

<div class="bg-primary-500 rounded-md shadow-md">

    <x-card-image-carrousel :medias="$activity->getMediaDisplay()" />

    <a href="{{ route('travel.activity.show', ['travelId' => $travel->id, 'activityId' => $activity->id]) }}" wire:navigate class="flex flex-col p-4">
        <h2 class="text-lg font-semibold text-zinc-800">
            {{ $activity->name }}
        </h2>

        <p class="text-zinc-600">
            {{ $activity->description }}
        </p>


        <x-show-place-name :placeName="$activity->place_name" />

        <x-show-url :url="$activity->url" />

        <x-show-price :priceByGroup="$activity->price_by_group" :priceByPerson="$activity->price_by_person" />

        <x-show-date :startDate="$activity->start_date" :endDate="$activity->end_date" :startTime="$activity->start_time" :endTime="$activity->endTime" />
    </a>
</div>
