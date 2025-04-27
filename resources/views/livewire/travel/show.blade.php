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
        <p>{{$travel->start_date}}</p>
        <p>{{$travel->end_date}}</p>

    </div>

</div>
