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

    <div class="flex flex-col gap-10">
        @if ($active->isNotEmpty())
            <x-travel.list :travels="$active" title="Actifs" />
        @endif

        @if ($upcoming->isNotEmpty())
            <x-travel.list :travels="$upcoming" title="À venir" />
        @endif

        @if ($past->isNotEmpty())
            <x-travel.list :travels="$past" title="Passés" />
        @endif

        @if ($noDate->isNotEmpty())
            <x-travel.list :travels="$noDate" title="Pas de date renseignée" />
        @endif
    </div>

    @unless (!$isResultEmpty)
        <p class="text-center text-zinc-500 dark:text-zinc-400">Vous n'avez aucun voyage pour le moment.</p>
    @endunless

</div>
