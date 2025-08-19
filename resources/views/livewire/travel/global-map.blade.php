<flux:field>
    <div
        class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200"
        role="region"
        aria-label="Carte globale du voyage">
        <div wire:ignore x-data="mapComponent()" x-init="initMap" x-on:activities-refreshed.window="refreshMarkers($event.detail, 'activity')" x-on:housings-refreshed.window="refreshMarkers($event.detail, 'housing')" x-on:focus-map-marker.window="focusMarker($event.detail.latitude, $event.detail.longitude)"
            role="application"
            aria-label="Carte interactive du voyage">
            <div class="w-full h-[60vh] rounded-lg" x-ref="mapContainer" role="img" aria-label="Carte montrant tous les lieux du voyage"></div>
        </div>
    </div>
</flux:field>

@push('scripts')
<script>
    function mapComponent() {
        return {
            map: null,
            activityMarkers: [],
            housingMarkers: [],

            initMap() {
                this.$nextTick(() => {
                    const container = this.$refs.mapContainer;

                    if (!container) {
                        console.error("Map container not found!");
                        return;
                    }

                    let travelLat = {!! json_encode($travel->place_latitude) !!}
                    let travelLon = {!! json_encode($travel->place_longitude) !!}
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

                    this.addMarkers({!! json_encode($activities) !!}, 'activity');
                    this.addMarkers({!! json_encode($housings) !!}, 'housing');
                });
            },

            addMarkers(items, type) {
                let iconUrl = '/images/markers/aeroz-blue.png';

                if (type === 'activity') {
                    iconUrl = '/images/markers/activity-orange.png';
                } else if (type === 'housing') {
                    iconUrl = '/images/markers/housing-green.png';
                }

                const customIcon = L.icon({
                    iconUrl: iconUrl,
                    iconSize: [30, 40],
                    iconAnchor: [15, 40],
                    popupAnchor: [0, -40],
                });

                items.forEach(item => {
                    const lat = item.place_latitude;
                    const lon = item.place_longitude;
                    const name = item.name;

                    if (lat && lon) {
                        const marker = L.marker([lat, lon], { icon: customIcon })
                            .addTo(this.map)
                            .bindPopup(name);

                        if (type === 'activity') {
                            this.activityMarkers.push(marker);
                        } else if (type === 'housing') {
                            this.housingMarkers.push(marker);
                        }
                    }
                });

                this.fitAllMarkers();
            },

            fitAllMarkers() {
                let bounds = L.latLngBounds();

                if (this.activityMarkers.length > 0 || this.housingMarkers.length > 0) {
                    this.activityMarkers.forEach(marker => {
                        if (marker.getLatLng()) {
                            bounds.extend(marker.getLatLng());
                        }
                    });
                    this.housingMarkers.forEach(marker => {
                        if (marker.getLatLng()) {
                            bounds.extend(marker.getLatLng());
                        }
                    });

                    this.map.fitBounds(bounds, { padding: [20, 20] });
                }
            },

            focusMarker(lat, lon) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

                if (lat && lon) {
                    this.map.setView([lat, lon], 12);
                }

            },

            clearMarkers(type) {
                if (type === 'activity') {
                    this.activityMarkers.forEach(marker => this.map.removeLayer(marker));
                    this.activityMarkers = [];
                } else if (type === 'housing') {
                    this.housingMarkers.forEach(marker => this.map.removeLayer(marker));
                    this.housingMarkers = [];
                }
            },

            refreshMarkers(newItems, type) {
                this.clearMarkers(type);
                this.addMarkers(newItems[0], type);
            },
        };
    }
</script>
@endpush

