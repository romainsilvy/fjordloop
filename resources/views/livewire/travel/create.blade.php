<flux:modal name="create-travel" class="w-full max-w-4xl mt-10" id="create-travel-modal">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Créer un voyage</flux:heading>
        </div>

        <flux:input label="Nom" placeholder="Nom du voyage" wire:model="name" />

        <livewire:travel.members-selector wire:model="members" />

        <livewire:search-map wire:model="place" modalId='create-travel-modal'/>

        <livewire:date-range-picker wire:model="dateRange"/>

        <div class="flex">
            <flux:spacer />

            <flux:button wire:click="save" variant="primary">Créer</flux:button>
        </div>
    </div>
</flux:modal>
