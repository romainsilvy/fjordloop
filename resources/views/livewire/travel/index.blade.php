<div>
    <div class="flex">
        <flux:heading size="xl">Voyages</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="create-travel">
            <flux:button>Nouveau</flux:button>
        </flux:modal.trigger>
    </div>

    <livewire:travel.create />


    <flux:separator variant="subtle" class="my-8" />

    <div class="flex flex-col">
        @forelse ($sections as $section)
            <x-travel.list :travels="$section['travels']" :title="$section['title']" />

            @if (!$loop->last)
                <flux:separator variant="subtle" class="my-8" />
            @endif
        @empty
            <p class="text-center text-zinc-500">Vous n'avez aucun voyage pour le moment.</p>
        @endforelse
    </div>

</div>
