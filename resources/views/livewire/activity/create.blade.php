<flux:modal name="create-activity" class="w-full max-w-4xl mt-10" wire:close="cleanupFields" id="create-activity-modal" role="dialog" aria-labelledby="create-activity-title" aria-describedby="create-activity-description">
    <div x-data="activityCreateCleanup()" class="space-y-6" role="form" aria-labelledby="create-activity-title">
        <div>
            <flux:heading size="lg" id="create-activity-title">Créer une activité</flux:heading>
            <p id="create-activity-description" class="sr-only">Formulaire pour créer une nouvelle activité</p>
        </div>

        <flux:input label="Nom" placeholder="Nom de l'activité" wire:model="name" aria-required="true" />
        <flux:textarea label="Description" placeholder="Description" wire:model="description" />
        <flux:input type="url" label="Url" placeholder="Url de l'activité" wire:model="url" />

        <x-upload-image-carrousel :images="$tempImages" inputId="upload-image-carrousel-activity-create" modalId="create-activity-modal" />

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
                    @if (!$startDate)
                        <flux:select.option value="">pas de date</flux:select.option>
                    @endif
                    @foreach ($availableEndDates as $key => $date)
                        <flux:select.option :value="$key">{{ $date }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input type="time" wire:model.live="endTime" aria-label="Heure de fin" />
            </flux:input.group>
        </div>

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

        <livewire:search-map wire:model="place" />

        <div class="flex">
            <flux:spacer />

            <flux:button wire:click="save" variant="primary" aria-label="Créer l'activité">Créer</flux:button>
        </div>
    </div>
</flux:modal>

<script>
    function activityCreateCleanup() {
        return {
            init() {
                const modal = this.$root.closest('dialog');

                if (!modal) return;

                const observer = new MutationObserver((mutationsList) => {
                    for (const mutation of mutationsList) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'open') {
                            if (!modal.hasAttribute('open')) {
                                @this.cleanupFields();
                            }
                        }
                    }
                });

                observer.observe(modal, {
                    attributes: true
                });
            },
        }
    }
</script>
