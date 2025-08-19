<div role="main" aria-labelledby="activity-detail-title">
    <flux:breadcrumbs class="mb-4" aria-label="Navigation vers l'activité">
        <flux:breadcrumbs.item href="{{ route('travel.index') }}">Voyages</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('travel.show', $travel->id) }}">{{ ucfirst($travel->name) }}
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Activités</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ ucfirst($activity->name) }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-col gap-4">
        <div class="flex">
            <flux:heading size="xl" id="activity-detail-title">{{ ucfirst($activity->name) }}</flux:heading>

            <flux:spacer />

            <flux:modal.trigger name="update-activity">
                <flux:button aria-label="Modifier l'activité {{ $activity->name }}">Modifier</flux:button>
            </flux:modal.trigger>
        </div>

        <div class="grid grid-cols-2 gap-4" role="region" aria-label="Images et carte de l'activité">
            <x-card-image-carrousel :medias="$medias" customHeight="h-[50vh]" />

            <flux:field class="w-full">
                <div
                    class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200">
                    <div wire:ignore x-data="showDetailMap(@js($activity->place_latitude), @js($activity->place_longitude), @js($activity->travel->place_latitude), @js($activity->travel->place_longitude))" x-init="initMap"
                        x-on:activity-refreshed.window="refreshMarker($event.detail)"
                        role="application"
                        aria-label="Carte de l'activité">
                        <div class="w-full h-[50vh] rounded-lg" x-ref="mapContainer" role="img" aria-label="Carte montrant l'emplacement de l'activité"></div>
                    </div>
                </div>
            </flux:field>
        </div>

        <div class="flex flex-col" role="region" aria-label="Détails de l'activité">
            <x-show-place-name :placeName="$activity->place_name" />

            <x-show-url clickable full :url="$activity->url" />

            <x-show-price :priceByGroup="$activity->price_by_group" :priceByPerson="$activity->price_by_person" />

            <x-show-date :startDate="$activity->start_date" :endDate="$activity->end_date" :startTime="$activity->start_time" :endTime="$activity->end_time" />

            <flux:separator class="mb-5" aria-hidden="true" />

            <p>
                {!! nl2br($activity->description) !!}
            </p>
        </div>
    </div>

    <livewire:activity.update :activity="$activity" />
</div>
