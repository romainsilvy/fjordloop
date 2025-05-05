@props(['housing', 'travel'])

<div class="bg-primary-500 rounded-md shadow-md">

    {{-- <x-activity.card-carrousel :activity="$activity" />

    <a href="{{ route('travel.activity.show', ['travelId' => $travel->id, 'activityId' => $activity->id]) }}" wire:navigate class="flex flex-col p-4">
        <h2 class="text-lg font-semibold text-zinc-800">
            {{ $activity->name }}
        </h2>

        <p class="text-zinc-600">
            {{ $activity->description }}
        </p>

        <x-activity.show-place-name :activity="$activity" />

        <x-activity.show-url :activity="$activity" />

        <x-activity.show-price :activity="$activity" />

        <x-activity.show-date :activity="$activity" />
    </a> --}}
</div>
