<flux:field>

    <div
        class="w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5">
        <div wire:ignore x-data="mapComponent()" x-init="initMap" x-on:activities-refreshed.window="refreshMarkers($event.detail)">
            <div class="w-full h-[60vh] rounded-lg" x-ref="mapContainer"></div>
        </div>
    </div>
</flux:field>

@push('scripts')
<script>
    function mapComponent() {
        return {
            map: null,
            markers: [],
            bounds: null,

            initMap() {
                this.$nextTick(() => {
                    const container = this.$refs.mapContainer;

                    if (!container) {
                        console.error("Map container not found!");
                        return;
                    }

                    let travelLat = @json($travel->place_latitude);
                    let travelLon = @json($travel->place_longitude);
                    let zoom = 12;

                    if (!travelLat && !travelLon) {
                        travelLat = 46.6034;
                        travelLon = 1.8883;
                        zoom = 5;
                    }

                    this.map = L.map(container).setView([travelLat, travelLon], zoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    this.bounds = L.latLngBounds();

                    this.addMarkers(@json($activities));
                });
            },

            addMarkers(activities) {
                const customIcon = L.icon({
                    iconUrl: '/images/markers/travel-orange.png',
                    iconSize: [30, 40],
                    iconAnchor: [15, 40],
                    popupAnchor: [0, -40],
                });

                activities.forEach(activity => {
                    const lat = activity.place_latitude;
                    const lon = activity.place_longitude;
                    const name = activity.name;

                    if (lat && lon) {
                        const marker = L.marker([lat, lon], { icon: customIcon })
                            .addTo(this.map)
                            .bindPopup(name);

                        this.markers.push(marker);
                        this.bounds.extend([lat, lon]);
                    }
                });

                if (this.bounds.isValid()) {
                    this.map.fitBounds(this.bounds, { padding: [20, 20] });
                }
            },

            clearMarkers() {
                this.bounds = L.latLngBounds();
                this.markers.forEach(marker => this.map.removeLayer(marker));
                this.markers = [];
            },

            refreshMarkers(newActivities) {
                this.clearMarkers();
                this.addMarkers(newActivities[0]);
            }
        };
    }
</script>
@endpush

