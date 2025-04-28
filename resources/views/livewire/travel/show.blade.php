<div>
    <div class="flex">
        <flux:heading size="xl">{{ ucfirst($travel->name)}}</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="update-travel">
            <flux:button>Modifier</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- <livewire:travel.create /> --}}


    <flux:separator variant="subtle" class="my-8" />

    <div class="flex flex-col gap-10">
        <p>{{$travel->name}}</p>
        <p>{{$travel->place_name}}</p>

        <x-travel.show-date :travel="$travel" />
    </div>

    <x-activity.list :activities="$activities" title="Activités" />
</div>
