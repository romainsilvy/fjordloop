<div>
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('travel.index') }}">Voyages</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ ucfirst($travel->name) }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>


    <div class="flex flex-col gap-4">
        <div class="flex flex-col">
            <flux:heading size="xl">{{ ucfirst($travel->name) }}</flux:heading>

            @if ($travel->place_name)
                <div class="flex flex-row items-center justify-start gap-2">
                    <flux:icon.map-pin class="size-4" />
                    <p class="text-sm">{{ $travel->place_name }}
                </div>
            @endif

            <x-travel.show-date :travel="$travel" />
        </div>
        <flux:separator variant="subtle" />


        <livewire:travel.global-map :travel="$travel" />

        <flux:separator variant="subtle" />

        <x-activity.list :activities="$activities" title="Activités" :travel="$travel" />


    </div>

    {{-- Modals --}}
    <livewire:activity.create :travel="$travel" />

</div>
