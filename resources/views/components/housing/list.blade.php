@props(['housings' => [], 'title', 'travel'])

<div class="flex flex-col gap-6">
    <div class="flex">
        <flux:heading size="xl" class="text-zinc-800 pl-4">
            {{ $title }}
        </flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="create-housing">
            <flux:button>Ajouter</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($housings as $housing)
            <x-housing.card :housing="$housing" :travel="$travel" />
        @empty
            <p class="ml-4 text-black">Vous n'avez aucun logement pour le moment.</p>
        @endforelse
    </div>
</div>
