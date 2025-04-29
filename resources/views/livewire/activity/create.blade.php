<flux:modal name="create-activity" class="w-full max-w-4xl mt-10" id="create-activity-modal">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Créer une activité</flux:heading>
        </div>

        <flux:input label="Nom" placeholder="Nom de l'activité" wire:model="name" />
        <flux:input label="Description" placeholder="Description" wire:model="description" />


        {{-- <livewire:travel.members-selector wire:model="members" />

        <livewire:search-map wire:model="place"/>

        <livewire:date-range-picker wire:model="dateRange"/> --}}

        <div class="flex">
            <flux:spacer />

            <flux:button wire:click="save" variant="primary">Créer</flux:button>
        </div>
    </div>
</flux:modal>
