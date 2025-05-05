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

        <x-card-image-carrousel :medias="$housing->getMediaDisplay()" customHeight="h-[50vh]" />




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

    {{-- <livewire:housing.update :housing="$housing" /> --}}
</div>
