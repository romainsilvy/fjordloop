<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-primary-50">
        <flux:sidebar sticky stashable class="border-r border-primary-300 bg-primary-200 shadow-xl">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.item icon="globe-europe-africa" :href="route('travel.index')" :current="request()->routeIs('travel.*')" wire:navigate>{{ __('Voyages') }}</flux:navlist.item>

            </flux:navlist>

            <flux:spacer />

            <flux:navlist.item
                icon="envelope"
                href="mailto:contact@tondomaine.com">
                {{ __('Nous contacter') }}
            </flux:navlist.item>

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile avatar:color="sky"
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar initials=" {{ auth()->user()->initials() }}" color="sky" />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    avatar:color="sky"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar initials=" {{ auth()->user()->initials() }}" color="sky" />


                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
        <div x-persist="toaster">
            <style>
                #toaster {
                    left: 0;
                }
            </style>
            <x-toaster-hub />
        </div>

        <script>
            function uploadCarrousel(modalId, inputId) {
                return {
                    isScrollable: false,
                    modalId: modalId,
                    inputId: inputId,

                    init() {
                        this.checkScroll();

                        const observer = new MutationObserver(() => this.checkScroll());
                        observer.observe(this.$refs.carousel, {
                            childList: true,
                            subtree: true
                        });

                        const modal = document.getElementById(this.modalId).firstElementChild;
                        const modalObserver = new MutationObserver(() => {
                            if (modal.hasAttribute('open')) {
                                this.setupPasteHandler();
                            } else {
                                this.cleanupPasteHandler();
                            }
                        });
                        modalObserver.observe(modal, {
                            attributes: true
                        });
                    },

                    checkScroll() {
                        const container = this.$refs.carousel;
                        this.isScrollable = container.scrollWidth > container.clientWidth;
                    },
                    setupPasteHandler() {
                        this._boundHandlePaste = this.handlePaste.bind(this);
                        document.addEventListener('paste', this._boundHandlePaste);

                    },
                    cleanupPasteHandler() {
                        document.removeEventListener('paste', this._boundHandlePaste);
                    },
                    handlePaste(event) {
                        const items = (event.clipboardData || event.originalEvent.clipboardData).items;
                        let hasProcessedImage = false;

                        for (let index in items) {
                            const item = items[index];

                            if (item.kind === 'file') {
                                const file = item.getAsFile();

                                if (file && file.type.startsWith('image/') && !hasProcessedImage) {
                                    const dataTransfer = new DataTransfer();
                                    dataTransfer.items.add(file);
                                    const fileInput = document.getElementById(this.inputId);
                                    fileInput.files = dataTransfer.files;

                                    // Trigger the change event on the file input
                                    const event = new Event('change', {
                                        bubbles: true
                                    });

                                    fileInput.dispatchEvent(event);

                                    // Flag to prevent duplicate processing in the same paste event
                                    hasProcessedImage = true;
                                }
                            }
                        }
                    },

                }
            }
        </script>

    <script>
        function showDetailMap(itemLat, itemLon, travelLat, travelLon) {
            return {
                map: null,
                marker: null,
                itemLat: itemLat,
                itemLon: itemLon,
                travelLat: travelLat,
                travelLon: travelLon,
                customIcon: null,

                initMap() {
                    this.$nextTick(() => {
                        const container = this.$refs.mapContainer;

                        if (!container) {
                            console.error("Map container not found!");
                            return;
                        }

                        this.map = L.map(container).setView([46.6034, 1.8883], 5);


                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.map);

                        this.customIcon = L.icon({
                            iconUrl: '/images/markers/activity-orange.png',
                            iconSize: [30, 40],
                            iconAnchor: [15, 40],
                            popupAnchor: [0, -40],
                        });

                        if (this.itemLat && this.itemLon) {
                            this.marker = L.marker([this.itemLat, this.itemLon], {
                                    icon: this.customIcon
                                })
                                .addTo(this.map);
                            this.map.setView([this.itemLat, this.itemLon], 12)

                        } else if(this.travelLat, this.travelLon) {
                            this.map.setView([this.travelLat, this.travelLon], 12)
                        }
                    });
                },
                refreshMarker(event) {
                    const lat = event[0].place_latitude;
                    const lon = event[0].place_longitude;

                    if (this.map && lat && lon) {
                        if (this.marker) {
                            this.marker.setLatLng([lat, lon]);
                        } else {
                            this.marker = L.marker([lat, lon], {
                                    icon: this.customIcon
                                })
                                .addTo(this.map);
                        }

                        this.map.setView([lat, lon], 12);
                    }
                }
            };
        }
    </script>

        @stack('scripts')



        @fluxScripts
    </body>
</html>
