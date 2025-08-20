<flux:modal name="create-housing" class="w-full max-w-4xl mt-10" wire:close="cleanupFields" id="create-housing-modal" role="dialog" aria-labelledby="create-housing-title" aria-describedby="create-housing-description">
    <div x-data="housingCreateCleanup()" class="space-y-6" role="form" aria-labelledby="create-housing-title">
        <div>
            <flux:heading size="lg" id="create-housing-title">Créer un logement</flux:heading>
            <p id="create-housing-description" class="sr-only">Formulaire pour créer un nouveau logement</p>
        </div>

        <flux:input
            label="Nom"
            placeholder="Nom du logement"
            wire:model="name"
            aria-required="true"
            description="Entrez le nom du logement (ex: Hôtel Central, Appartement Vue Mer)" />

        <flux:textarea
            label="Description"
            placeholder="Description"
            wire:model="description"
            description="Décrivez le logement, ses équipements et ses caractéristiques" />

        <flux:input
            type="url"
            label="Url"
            placeholder="Url du logement"
            wire:model="url"
            description="Lien vers le site web de réservation ou d'information (optionnel)" />

        <x-upload-image-carrousel :images="$tempImages" inputId="upload-image-carrousel-housing-create" modalId="create-housing-modal" />

        <div class="*:w-1/2 flex items-center gap-4" role="group" aria-label="Dates et heures du logement">
            <flux:input.group label="Début" description="Date et heure d'arrivée au logement">
                <flux:select class="max-w-fit" wire:model.live="startDate" aria-label="Date de début">
                    <flux:select.option value="">pas de date</flux:select.option>
                    @foreach ($availableStartDates as $key => $date)
                        <flux:select.option :value="$key">{{ $date }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input type="time" wire:model.live="startTime" aria-label="Heure de début" />
            </flux:input.group>

            <flux:input.group label="Fin" description="Date et heure de départ du logement">
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

        <div class="*:w-1/2" role="group" aria-label="Prix du logement">
            <flux:input.group label="Prix" description="Informations sur le coût du logement">
                <flux:input type="number" placeholder="99.99" wire:model="price" aria-label="Montant du prix"/>

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

            <flux:button wire:click="save" variant="primary" aria-label="Créer le logement">Créer</flux:button>
        </div>
    </div>
</flux:modal>

<script>
    function housingCreateCleanup() {
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
