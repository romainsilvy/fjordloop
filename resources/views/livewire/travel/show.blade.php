<div role="main" aria-labelledby="travel-detail-title">
    <flux:breadcrumbs class="mb-4" aria-label="Navigation vers le voyage">
        <flux:breadcrumbs.item href="{{ route('travel.index') }}">Voyages</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ ucfirst($travel->name) }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-col gap-4">
        <div class="flex">
            <div class="flex flex-col">
                <flux:heading size="xl" id="travel-detail-title">{{ ucfirst($travel->name) }}</flux:heading>

                @if ($travel->place_name)
                    <div class="flex flex-row items-center justify-start gap-2" role="text" aria-label="Lieu du voyage">
                        <flux:icon.map-pin class="size-4" aria-hidden="true" />
                        <p class="text-sm">{{ $travel->place_name }}
                    </div>
                @endif

                <x-travel.show-date :travel="$travel" />
            </div>

            <flux:spacer />

            <flux:modal.trigger name="update-travel">
                <flux:button aria-label="Modifier le voyage {{ $travel->name }}">Modifier</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" aria-hidden="true" />

        <livewire:travel.global-map :travel="$travel" />

        <div x-data="{ tab: 'week-calendar' }" class="flex flex-col gap-4 justify-start items-center min-h-[100vh]" role="tablist" aria-label="Navigation des onglets du voyage">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px" role="tablist" aria-label="Onglets de navigation">
                    <button @click="tab = 'week-calendar'"
                            :class="tab === 'week-calendar' ? 'border-primary-700 text-primary-700' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 cursor-pointer'"
                            class="py-4 px-6 font-medium text-sm border-b-2 transition-colors duration-200 ease-out focus:outline-none"
                            role="tab"
                            aria-selected="tab === 'week-calendar'"
                            aria-controls="week-calendar-panel"
                            aria-label="Calendrier semaine">
                        Calendrier semaine
                    </button>
                    <button @click="tab = 'month-calendar'"
                            :class="tab === 'month-calendar' ? 'border-primary-700 text-primary-700' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 cursor-pointer'"
                            class="py-4 px-6 font-medium text-sm border-b-2 transition-colors duration-200 ease-out focus:outline-none"
                            role="tab"
                            aria-selected="tab === 'month-calendar'"
                            aria-controls="month-calendar-panel"
                            aria-label="Calendrier mois">
                        Calendrier Mois
                    </button>
                    <button @click="tab = 'activities'"
                            :class="tab === 'activities' ? 'border-primary-700 text-primary-700' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 cursor-pointer'"
                            class="py-4 px-6 font-medium text-sm border-b-2 transition-colors duration-200 ease-out focus:outline-none"
                            role="tab"
                            aria-selected="tab === 'activities'"
                            aria-controls="activities-panel"
                            aria-label="Activités">
                        Activités
                    </button>
                    <button @click="tab = 'housings'"
                            :class="tab === 'housings' ? 'border-primary-700 text-primary-700' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 cursor-pointer'"
                            class="py-4 px-6 font-medium text-sm border-b-2 transition-colors duration-200 ease-out focus:outline-none"
                            role="tab"
                            aria-selected="tab === 'housings'"
                            aria-controls="housings-panel"
                            aria-label="Logements">
                        Logements
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div x-show="tab === 'week-calendar'" class="w-full flex-1 flex min-h-0" role="tabpanel" id="week-calendar-panel" aria-labelledby="week-calendar-tab">
                <livewire:week-calendar :travel="$travel" />
            </div>
            <div x-show="tab === 'month-calendar'" class="w-full flex-1 flex min-h-0" role="tabpanel" id="month-calendar-panel" aria-labelledby="month-calendar-tab">
                <livewire:month-calendar :travel="$travel" />
            </div>
            <div x-show="tab === 'activities'" class="w-full flex-1 flex min-h-0" role="tabpanel" id="activities-panel" aria-labelledby="activities-tab">
                <x-activity.list :activities="$activities" title="Activités" :travel="$travel" />
                <livewire:activity.create :travel="$travel" />
            </div>
            <div x-show="tab === 'housings'" class="w-full flex-1 flex min-h-0" role="tabpanel" id="housings-panel" aria-labelledby="housings-tab">
                <x-housing.list title="Logements" :travel="$travel" :housings="$housings" />
                <livewire:housing.create :travel="$travel" />
            </div>
        </div>
    </div>

    {{-- Modals --}}
    <livewire:travel.update :travel="$travel" />

</div>
