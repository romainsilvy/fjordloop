<flux:modal name="create-travel" class="w-full max-w-4xl mt-10">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Créer un voyage</flux:heading>
        </div>

        <flux:input label="Nom" placeholder="Nom du voyage" wire:model="name" />
        <flux:input label="Lieu" placeholder="Adresse" wire:model="placeName" />

        {{-- integrer membersselector + datepicker --}}

        <livewire:travel.members-selector wire:model="members" />

        <div class="flex *:w-1/2 gap-4">
            <flux:input label="Date de début" placeholder="Date de début" wire:model="startDate" type="date" />
            <flux:input label="Date de fin" placeholder="Date de fin" wire:model="endDate" type="date" />
        </div>


        <div class="flex">
            <flux:spacer />

            <flux:button wire:click="save" variant="primary">Créer</flux:button>
        </div>
    </div>
</flux:modal>
