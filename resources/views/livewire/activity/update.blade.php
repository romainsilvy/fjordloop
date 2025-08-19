<flux:modal name="update-activity" class="w-full max-w-4xl mt-10 max-h-[90vh]" wire:close="cleanupFields" id="update-activity-modal"
    :dismissible="false" role="dialog" aria-labelledby="update-activity-title" aria-describedby="update-activity-description">
    <div class="space-y-6" role="form" aria-labelledby="update-activity-title">
        <div>
            <flux:heading size="lg" id="update-activity-title">Modifier l'activité {{ $activity?->name }}</flux:heading>
            <p id="update-activity-description" class="sr-only">Formulaire pour modifier l'activité</p>
        </div>

        <flux:input label="Nom" placeholder="Nom de l'activité" wire:model="name" aria-required="true" />
        <flux:textarea label="Description" placeholder="Description" wire:model="description" />

        <x-upload-image-carrousel :images="$tempImages" :existingImages="$existingMedia" inputId="upload-image-carrousel-activity-update" modalId="update-activity-modal" />

        <livewire:search-map wire:model="place" />

        <flux:input type="url" label="Url" placeholder="Url de l'activité" wire:model="url" />

        @if ($activity)
            <div class="*:w-1/2 flex items-center gap-4" role="group" aria-label="Dates et heures de l'activité">
                <flux:input.group label="Début">
                    <flux:select class="max-w-fit" wire:model.live="startDate" aria-label="Date de début">
                        <flux:select.option value="">pas de date</flux:select.option>
                        @foreach ($availableStartDates as $key => $date)
                            <flux:select.option :value="$key">{{ $date }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input type="time" wire:model.live="startTime" aria-label="Heure de début" />
                </flux:input.group>

                <flux:input.group label="Fin">
                    <flux:select class="max-w-fit" wire:model.live="endDate" aria-label="Date de fin">
                        @foreach ($availableEndDates as $key => $date)
                            <flux:select.option :value="$key">{{ $date }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input type="time" wire:model.live="endTime" aria-label="Heure de fin" />
                </flux:input.group>
            </div>
        @endif

        <div class="*:w-1/2" role="group" aria-label="Prix de l'activité">
            <flux:input.group label="Prix">
                <flux:input type="number" placeholder="99.99" wire:model="price" aria-label="Montant du prix" />

                <flux:select class="max-w-fit" wire:model="priceType" aria-label="Type de prix">
                    @foreach ($availablePrices as $key => $availablePrice)
                        <flux:select.option :value="$key">{{ $availablePrice }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:input.group>
        </div>

        <div class="flex">
            <flux:button wire:confirm="Êtes vous sur de vouloir supprimer cette activité ?" wire:click="delete"
                variant="danger" aria-label="Supprimer l'activité {{ $activity?->name }}">Supprimer</flux:button>

            <flux:spacer />

            <flux:button wire:click="save" variant="primary" aria-label="Sauvegarder les modifications">Modifier</flux:button>
        </div>
    </div>
</flux:modal>
