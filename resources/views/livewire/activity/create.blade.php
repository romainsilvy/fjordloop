<flux:modal name="create-activity" class="w-full max-w-4xl mt-10" wire:close="cleanupFields" :dismissible="false">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Créer une activité</flux:heading>
        </div>

        <flux:input label="Nom" placeholder="Nom de l'activité" wire:model="name" />
        <flux:textarea label="Description" placeholder="Description" wire:model="description" />
        <flux:input type="url" label="Url" placeholder="Url de l'activité" wire:model="url" />

        <x-upload-image-carrousel :images="$tempImages" />


        <div class="*:w-1/2 flex items-center gap-4">

            <flux:input.group label="Début">
                <flux:select class="max-w-fit" wire:model.live="startDate">
                    <flux:select.option value="">pas de date</flux:select.option>
                    @foreach ($availableStartDates as $key => $date)
                        <flux:select.option :value="$key">{{ $date }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input type="time" wire:model.live="startTime" />
            </flux:input.group>

            <flux:input.group label="Fin">
                <flux:select class="max-w-fit" wire:model.live="endDate">
                    @if (!$startDate)
                    <flux:select.option value="">pas de date</flux:select.option>
                    @endif
                    @foreach ($availableEndDates as $key => $date)
                        <flux:select.option :value="$key">{{ $date }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input type="time" wire:model.live="endTime" />
            </flux:input.group>
        </div>

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

        <livewire:search-map wire:model="place" />


        <div class="flex">
            <flux:spacer />

            <flux:button wire:click="save" variant="primary">Créer</flux:button>
        </div>
    </div>
</flux:modal>
