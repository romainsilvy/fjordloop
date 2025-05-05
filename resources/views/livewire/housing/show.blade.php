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

        @if ($housing->place_latitude && $housing->place_longitude)
            <flux:field class="w-full">
                <div
                    class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200">
                    <div wire:ignore x-data="mapComponent()" x-init="initMap"
                        x-on:housing-refreshed.window="refreshMarker($event.detail)">
                        <div class="w-full h-[50vh] rounded-lg" x-ref="mapContainer"></div>
                    </div>
                </div>
            </flux:field>

            @push('scripts')
                <script>
                    function mapComponent() {
                        return {
                            map: null,
                            bounds: null,
                            marker: null,

                            initMap() {
                                this.$nextTick(() => {
                                    const container = this.$refs.mapContainer;

                                    if (!container) {
                                        console.error("Map container not found!");
                                        return;
                                    }

                                    let housingLat = @json($housing->place_latitude);
                                    let housingLon = @json($housing->place_longitude);
                                    let zoom = 12;

                                    this.map = L.map(container).setView([housingLat, housingLon], zoom);

                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                        attribution: '&copy; OpenStreetMap contributors'
                                    }).addTo(this.map);

                                    this.bounds = L.latLngBounds();

                                    const customIcon = L.icon({
                                        iconUrl: '/images/markers/housing-green.png',
                                        iconSize: [30, 40],
                                        iconAnchor: [15, 40],
                                        popupAnchor: [0, -40],
                                    });

                                    if (housingLat && housingLon) {
                                        this.marker = L.marker([housingLat, housingLon], {
                                                icon: customIcon
                                            })
                                            .addTo(this.map);
                                    }

                                });
                            },
                            refreshMarker(event) {
                                const lat = event[0].place_latitude;
                                const lon = event[0].place_longitude;

                                if (this.map) {
                                    this.map.setView([lat, lon], 12);
                                    this.marker.setLatLng([lat, lon]);
                                }
                            }
                        };
                    }
                </script>
            @endpush
        @endif
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
