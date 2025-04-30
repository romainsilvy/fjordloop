<div>
    <flux:heading size="xl">{{ ucfirst($travel->name) }}</flux:heading>

    {{-- <div class="flex">
        <flux:heading size="xl">{{ ucfirst($travel->name)}}</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="update-travel">
            <flux:button>Modifier</flux:button>
        </flux:modal.trigger>
    </div> --}}

    <livewire:activity.create :travel="$travel" />

    <div>
        <p class="text-zinc-500 dark:text-zinc-400 text-sm">{{ $travel->place_name }}
            <x-travel.show-date :travel="$travel" />
    </div>

    <flux:separator variant="subtle" class="my-8" />

    <div class="flex flex-col gap-8">
        <livewire:travel.global-map :travel="$travel" />

        <flux:separator variant="subtle" class="my-8" />

        <x-activity.list :activities="$activities" title="Activités" />

        <livewire:activity.update />

    </div>
</div>
