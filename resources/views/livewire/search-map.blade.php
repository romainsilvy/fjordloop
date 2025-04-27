<flux:field>
    <flux:label class="inline-flex items-center text-sm font-medium text-zinc-800 dark:text-white">
        Lieu
    </flux:label>

    <div
        class="w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5">

        <div class="py-2 px-3 w-full">
            <input type="text" wire:model.live.debounce.300ms="query" placeholder="Rechercher un lieu"
                class="w-full"></input>
        </div>



        @if (!empty($results))
            <div class="px-3">
                @foreach ($results as $result)
                    <p wire:click="selectLocation('{{ $result['lat'] }}', '{{ $result['lon'] }}', '{{ json_encode($result['geojson'] ?? null) }}', '{{ addslashes($result['display_name']) }}')"
                        class="text-xs my-1 cursor-pointer">
                        {{ $result['display_name'] }}
                    </p>
                    @if (!$loop->last)
                        <hr class="border-zinc-200 dark:border-white/10">
                    @endif
                @endforeach
            </div>
        @endif


        <div wire:ignore x-data="locationPicker()" x-on:location-selected.window="updateLocation($event.detail.lat, $event.detail.lon, $event.detail.geojson, $event.detail.name)">
            <div class="w-full h-64 rounded-b-lg" x-ref="map"></div>
        </div>
    </div>
</flux:field>

@push('scripts')
    <script>
        function locationPicker() {
            return {
                map: null,
                marker: null,
                geojsonLayer: null,

                init() {
                    const modal = document.getElementById('create-travel-modal').firstElementChild;
                    if (!modal) return;

                    const observer = new MutationObserver((mutationsList) => {
                        for (const mutation of mutationsList) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'open') {
                                if (modal.hasAttribute('open')) {
                                    console.log('Modal opened');
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
                    this.map = L.map(this.$refs.map).setView([51.1642, 10.4541194], 6);
                    const baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);
                },
                updateLocation(lat, lon, geojson, name) {
                    this.map.setView([lat, lon], 12);

                    if (this.marker) {
                        this.marker.setLatLng([lat, lon]);
                    } else {
                        this.marker = L.marker([lat, lon]).addTo(this.map);
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
            }
        }
    </script>
@endpush
