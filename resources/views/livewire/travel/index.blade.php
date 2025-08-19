<div role="main" aria-labelledby="travels-heading">
    <flux:breadcrumbs class="mb-4" aria-label="Navigation des voyages">
        <flux:breadcrumbs.item>Voyages</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex">
        <flux:heading size="xl" id="travels-heading">Vos voyages</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="create-travel">
            <flux:button aria-label="Créer un nouveau voyage">Nouveau</flux:button>
        </flux:modal.trigger>
    </div>

    <livewire:travel.create />

    <flux:separator variant="subtle" class="my-8" aria-hidden="true" />

    <div class="flex flex-col" role="region" aria-labelledby="travels-heading">
        @forelse ($sections as $section)
            <x-travel.list :travels="$section['travels']" :title="$section['title']" />

            @if (!$loop->last)
                <flux:separator variant="subtle" class="my-8" aria-hidden="true" />
            @endif
        @empty
            <p class="text-center text-zinc-500" role="status" aria-live="polite">Vous n'avez aucun voyage pour le moment.</p>
        @endforelse
    </div>

</div>
