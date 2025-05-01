<flux:modal name="update-activity" class="w-full max-w-4xl mt-10 max-h-[90vh]" wire:close="cleanupFields" :dismissible="false">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Modifier l'activité {{ $activity?->name }}</flux:heading>
        </div>

        <flux:input label="Nom" placeholder="Nom de l'activité" wire:model="name" />
        <flux:input label="Description" placeholder="Description" wire:model="description" />

        <x-upload-image-carrousel :images="$tempImages" :existingImages="$existingMedia" />

        <livewire:search-map wire:model="place" />

        <flux:input type="url" label="Url" placeholder="Url de l'activité" wire:model="url" />


        <div class="*:w-1/2">
            <flux:input.group label="Prix">
                <flux:input type="number" placeholder="99.99" wire:model="price" />

                <flux:select class="max-w-fit" wire:model="priceType">
                    @foreach ($availablePrices as $key => $availablePrice)
                        <flux:select.option :value="$key">{{ $availablePrice }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:input.group>
        </div>


        <div class="flex">
            <flux:button wire:confirm="Êtes vous sur de vouloir supprimer cette activité ?" wire:click="delete"
                variant="danger">Supprimer</flux:button>

            <flux:spacer />

            <flux:button wire:click="save" variant="primary">Modifier</flux:button>
        </div>
    </div>
</flux:modal>
