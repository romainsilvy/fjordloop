<div>
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('travel.index') }}">Voyages</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('travel.show', $travel->id) }}">{{ ucfirst($travel->name) }}
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Logements</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ ucfirst($housing->name) }}</flux:breadcrumbs.item>


    </flux:breadcrumbs>

    <div class="flex flex-col gap-4">
        <div class="flex">
            <flux:heading size="xl">{{ ucfirst($housing->name) }}</flux:heading>

            <flux:spacer />

            <flux:modal.trigger name="update-housing">
                <flux:button>Modifier</flux:button>
            </flux:modal.trigger>
        </div>

        <div class="grid grid-cols-2 gap-4">

        <x-card-image-carrousel :medias="$housing->getMediaDisplay()" customHeight="h-[50vh]" />

            <flux:field class="w-full">
                <div
                    class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200">
                    <div wire:ignore x-data="showDetailMap(@js($housing->place_latitude), @js($housing->place_longitude), @js($housing->travel->place_latitude), @js($housing->travel->place_longitude))" x-init="initMap"
                        x-on:housing-refreshed.window="refreshMarker($event.detail)">
                        <div class="w-full h-[50vh] rounded-lg" x-ref="mapContainer"></div>
                    </div>
                </div>
            </flux:field>

        </div>


        <div class="flex flex-col">
            <x-show-place-name :placeName="$housing->place_name" />

            <x-show-url :url="$housing->url" />

            <x-show-price :priceByGroup="$housing->price_by_group" :priceByPerson="$housing->price_by_person" />

            <x-show-date :startDate="$housing->start_date" :endDate="$housing->end_date" :startTime="$housing->start_time" :endTime="$housing->endTime" />

            <flux:separator class="mb-5" />

            <p>
                {!! nl2br($housing->description) !!}
            </p>
        </div>
    </div>

    <livewire:housing.update :housing="$housing" />
</div>
