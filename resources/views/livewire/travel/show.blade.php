<div>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col">
            <flux:heading size="xl">{{ ucfirst($travel->name) }}</flux:heading>

            <div class="flex flex-row items-center justify-start gap-2">
                <flux:icon.map-pin class="size-4" />
                <p class="text-sm">{{ $travel->place_name }}
            </div>
            
            <x-travel.show-date :travel="$travel" />
        </div>
        <flux:separator variant="subtle" />


        <livewire:travel.global-map :travel="$travel" />

        <flux:separator variant="subtle" />

        <x-activity.list :activities="$activities" title="Activités" />


    </div>

    {{-- Modals --}}
    <livewire:activity.update />
    <livewire:activity.create :travel="$travel" />

</div>
