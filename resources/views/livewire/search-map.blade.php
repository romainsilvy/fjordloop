<flux:field>
    <flux:label class="inline-flex items-center text-sm font-medium text-zinc-800">
        Lieu
    </flux:label>

    <div
        class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200">


        <div class="py-2 px-3 w-full relative block group/input">
            <input type="text" wire:model.live.debounce.300ms="query" placeholder="Rechercher un lieu"
                class="w-full"></input>

            <div wire:loading.delay wire:target="query"
                class="absolute top-1/2 end-3 -translate-y-1/2 transform flex items-center gap-x-1.5 text-xs text-zinc-400">
                <flux:icon.loading class="size-4" />
            </div>
        </div>


        @if (!empty($results))
            <div class="px-3">
                @foreach ($results as $result)
                    <p wire:click="selectLocation('{{ $result['lat'] }}', '{{ $result['lon'] }}', '{{ null }}', '{{ addslashes($result['display_name']) }}')"
                        class="text-xs my-1 cursor-pointer">
                        {{ $result['display_name'] }}
                    </p>
                    @if (!$loop->last)
                        <hr class="border-zinc-200">
                    @endif
                @endforeach
            </div>
        @endif


        <div wire:ignore x-data="locationPicker(@js($place))"
            x-on:location-selected.window="updateLocation($event.detail.lat, $event.detail.lon, $event.detail.geojson, $event.detail.name)"
            x-on:open-map.window="openMap($event.detail.lat, $event.detail.lon, $event.detail.geojson, $event.detail.name)"
            x-on:clean-map.window="cleanMap()">
            <div class="w-full h-64 rounded-b-lg" x-ref="map"></div>
        </div>
    </div>
</flux:field>

@push('scripts')
    <script>
        function locationPicker(place) {
            return {
                map: null,
                marker: null,
                geojsonLayer: null,
                existingPlace: place,

                init() {
                    const modal = this.$root.closest('dialog');

                    if (!modal) return;

                    const observer = new MutationObserver((mutationsList) => {
                        for (const mutation of mutationsList) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'open') {
                                if (modal.hasAttribute('open')) {
                                    this.initMap(); // Initialize map when modal opens
                                }
                            }
                        }
                    });

                    observer.observe(modal, {
                        attributes: true
                    });
                },
                initMap() {
                    this.map = L.map(this.$refs.map).setView([46.6034, 1.8883], 5);
                    const baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);


                    this.updateLocation(this.existingPlace.lat, this.existingPlace.lng, this.existingPlace.geojson, this
                        .existingPlace.display_name);
                },
                openMap(lat, lon, geojson, name) {
                    const modal = this.$root.closest('dialog');

                    // Check if modal is open and map is already initialized
                    if (modal && modal.hasAttribute('open') && this.map) {
                        this.updateLocation(lat, lon, geojson, name);
                    } else {
                        // Save data to use when map is initialized
                        this.existingPlace = {
                            lat: lat,
                            lng: lon,
                            geojson: geojson,
                            display_name: name
                        };
                    }
                },
                updateLocation(lat, lon, geojson, name) {
                    if (lat && lon && this.map) {
                        const customIcon = L.icon({
                            iconUrl: '/images/markers/travel-orange.png',
                            iconSize: [30, 40],
                            iconAnchor: [15, 40],
                            popupAnchor: [0, -40],
                        });

                        this.map.setView([lat, lon], 12);

                        if (this.marker) {
                            this.marker.setLatLng([lat, lon]);
                        } else {
                            this.marker = L.marker([lat, lon], { icon: customIcon }).addTo(this.map);
                        }


                        if (this.geojsonLayer) {
                            this.map.removeLayer(this.geojsonLayer);
                        }

                        if (geojson) {
                            this.geojsonLayer = L.geoJSON(JSON.parse(geojson), {
                                style: {
                                    color: '#2563eb',
                                    fillColor: '#60a5fa',
                                    fillOpacity: 0.4,
                                    weight: 2
                                }
                            }).addTo(this.map);

                            this.map.fitBounds(this.geojsonLayer.getBounds());
                        }
                    }
                },
                cleanMap() {
                    if (this.map) {
                        this.map.off();
                        this.map.remove();
                        this.map = null;
                    }
                }
            }
        }
    </script>
@endpush
