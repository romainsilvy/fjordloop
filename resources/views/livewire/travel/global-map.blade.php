<flux:field>

    <div
        class="w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5">
        <div wire:ignore x-data="{ activities: @js($activities) }" x-init="initMap($refs.map, activities)">
            <div class="w-full h-96 rounded-lg" x-ref="map"></div>
        </div>
    </div>
</flux:field>

@push('scripts')
    <script>
        function initMap(mapContainer, activities) {
            const map = L.map(mapContainer).setView([51.1642, 10.4541194], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const customIcon = L.icon({
                iconUrl: '/images/markers/travel-orange.png',
                iconSize: [30, 40],
                iconAnchor: [15, 40],
                popupAnchor: [0, -40],
            });

            const bounds = L.latLngBounds();

            activities.forEach(activity => {
                const lat = activity.place_latitude;
                const lon = activity.place_longitude;
                const name = activity.name;

                if (lat && lon) {
                    const marker = L.marker([lat, lon], {
                        icon: customIcon
                    }).addTo(map);
                    marker.bindPopup(name);
                    bounds.extend([lat, lon]);
                }
            });

            if (bounds.isValid()) {
                map.fitBounds(bounds, {
                    padding: [20, 20]
                });
            }
        }
    </script>
@endpush
