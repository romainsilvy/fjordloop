<div>
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('travel.index') }}">Voyages</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('travel.show', $travel->id) }}">{{ ucfirst($travel->name) }}
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Activités</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ ucfirst($activity->name) }}</flux:breadcrumbs.item>


    </flux:breadcrumbs>

    <div class="flex flex-col gap-4">
        <div class="flex">
            <flux:heading size="xl">{{ ucfirst($activity->name) }}</flux:heading>

            <flux:spacer />

            <flux:modal.trigger name="update-activity">
                <flux:button>Modifier</flux:button>
            </flux:modal.trigger>
        </div>

        <div class="grid grid-cols-2 gap-4 h-[50vh]">

            @if ($activity->getMediaDisplay() && count($activity->getMediaDisplay()) > 0)
                <div x-data="{
                    currentIndex: 0,
                    next() {
                        if (this.currentIndex < {{ count($activity->getMediaDisplay()) }} - 1) {
                            this.currentIndex++;
                        } else {
                            this.currentIndex = 0;
                        }
                    },
                    prev() {
                        if (this.currentIndex > 0) {
                            this.currentIndex--;
                        } else {
                            this.currentIndex = {{ count($activity->getMediaDisplay()) }} - 1;
                        }
                    }
                }" class="w-full">
                    <div class="relative">
                        @if (count($activity->getMediaDisplay()) > 1)
                            <button type="button"
                                class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
                                x-on:click="prev()">
                                <flux:icon.chevron-left />
                            </button>
                        @endif
                        <div class="carousel-container relative flex justify-center items-center overflow-hidden">
                            <template x-for="(media, index) in {{ $activity->getMediaDisplay() }}"
                                :key="index">
                                <div class="w-full h-[50vh] rounded-lg transition-all duration-500 bg-black/5"
                                    x-show="currentIndex === index">
                                    <img :src="media.url" class="w-full h-full object-contain"
                                        :alt="media.name || 'Activity image'" />
                                </div>
                            </template>
                        </div>
                        @if (count($activity->getMediaDisplay()) > 1)
                            <button type="button"
                                class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
                                x-on:click="next()">
                                <flux:icon.chevron-right />
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <div></div>
            @endif

            @if ($activity->place_latitude && $activity->place_longitude)
                <flux:field class="w-full">
                    <div
                        class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200">
                        <div wire:ignore x-data="mapComponent()" x-init="initMap"
                            x-on:activities-refreshed.window="refreshMarkers($event.detail)">
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

                                initMap() {
                                    this.$nextTick(() => {
                                        const container = this.$refs.mapContainer;

                                        if (!container) {
                                            console.error("Map container not found!");
                                            return;
                                        }

                                        let activityLat = @json($activity->place_latitude);
                                        let activityLon = @json($activity->place_longitude);
                                        let zoom = 12;

                                        this.map = L.map(container).setView([activityLat, activityLon], zoom);

                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '&copy; OpenStreetMap contributors'
                                        }).addTo(this.map);

                                        this.bounds = L.latLngBounds();

                                        const customIcon = L.icon({
                                            iconUrl: '/images/markers/travel-orange.png',
                                            iconSize: [30, 40],
                                            iconAnchor: [15, 40],
                                            popupAnchor: [0, -40],
                                        });

                                        if (activityLat && activityLon) {
                                            const marker = L.marker([activityLat, activityLon], {
                                                    icon: customIcon
                                                })
                                                .addTo(this.map);
                                        }

                                    });
                                },
                            };
                        }
                    </script>
                @endpush
            @endif


        </div>



        <div class="flex flex-col">


            <x-activity.show-place-name :activity="$activity" />

            <x-activity.show-url :activity="$activity" />

            <x-activity.show-price :activity="$activity" />

            <x-activity.show-date :activity="$activity" />

            <flux:separator class="mb-5"/>

            <p class="">
                {!! nl2br($activity->description) !!}
            </p>
        </div>
    </div>

    <livewire:activity.update :activity="$activity" />
</div>
