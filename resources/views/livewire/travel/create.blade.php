<flux:modal name="create-travel" class="w-full max-w-4xl mt-10" wire:close="cleanupFields" role="dialog" aria-labelledby="create-travel-title" aria-describedby="create-travel-description">
    <div class="space-y-6" role="form" aria-labelledby="create-travel-title">
        <div>
            <flux:heading size="lg" id="create-travel-title">Créer un voyage</flux:heading>
            <p id="create-travel-description" class="sr-only">Formulaire pour créer un nouveau voyage</p>
        </div>

        <flux:input label="Nom" placeholder="Nom du voyage" wire:model="name" aria-required="true" />

        <livewire:travel.members-selector wire:model="members" />

        <livewire:search-map wire:model="place"/>

        <livewire:date-range-picker wire:model="dateRange"/>

        <div class="flex">
            <flux:spacer />

            <flux:button wire:click="save" variant="primary" aria-label="Créer le voyage">Créer</flux:button>
        </div>
    </div>
</flux:modal>
