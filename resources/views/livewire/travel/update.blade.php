<flux:modal name="update-travel" class="w-full max-w-4xl mt-10" wire:close="cleanupFields">
    <div class="space-y-6" x-data="travelUpdateCleanup()">
        <div>
            <flux:heading size="lg">Modifier le voyage {{ $travel->name }}</flux:heading>
        </div>

        <flux:input label="Nom" placeholder="Nom du voyage" wire:model="name" />

        <livewire:search-map wire:model="place" />

        @if ($invitations->isNotEmpty())
            <flux:field>
                <flux:label class="inline-flex items-center text-sm font-medium text-zinc-800">
                    Invitations en attente
                </flux:label>

                <div
                    class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] ps-3 pe-3 bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200">

                    @foreach ($invitations as $invitation)
                        <div class="flex justify-between items-center">
                            <p class="text-xs my-1">{{ $invitation->email }}</p>
                            <div class="flex gap-2">
                                <flux:button size="xs" variant="danger" class="my-2"
                                    wire:click="deleteInvitation('{{ $invitation->id }}')"
                                    wire:confirm="Êtes vous sur de vouloir supprimer cette invitation ? L'utilisateur ne pourra plus rejoindre le voyage">
                                    Supprimer</flux:button>
                                <flux:button size="xs" class="my-2"
                                    wire:click="resendInvitation('{{ $invitation->id }}')"
                                    wire:confirm="En cliquant sur ce bouton {{ $invitation->email }} recevra une nouvelle invitation par e-mail. Voulez-vous continuer ?">
                                    Renvoyer</flux:button>
                            </div>
                        </div>
                        @if (!$loop->last)
                            <hr class="border-zinc-200">
                        @endif
                    @endforeach
                </div>
            </flux:field>
        @endif

        <flux:field>
            <flux:label class="inline-flex items-center text-sm font-medium text-zinc-800">
                Membres
            </flux:label>

            <div
                class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm min-h-10 leading-[1.375rem] ps-3 pe-3 bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200">

                @foreach ($members as $member)
                    <div class="flex justify-between items-center">
                        <p class="text-xs my-1">{{ $member->name }}</p>
                        <div class="flex gap-2 my-2">
                            @if ($member->id == auth()->id())
                            <flux:badge size="sm" color="cyan">moi</flux:badge>

                            @endif
                            @if ($travel->isOwner($member))
                                <flux:badge size="sm">créateur</flux:badge>
                            @endif
                            @if (!$travel->isOwner($member) && $member->id !== auth()->id())
                                <flux:button size="xs" variant="danger"
                                    wire:click="deleteMember('{{ $member->id }}')"
                                    wire:confirm="Êtes vous sur de vouloir supprimer {{ $member->name }} du voyage ?">
                                    Supprimer</flux:button>
                            @endif
                        </div>
                    </div>
                    @if (!$loop->last)
                        <hr class="border-zinc-200">
                    @endif
                @endforeach
            </div>
        </flux:field>


        <livewire:travel.members-selector wire:model="membersToInvite" title="Inviter de nouveaux membres" />


        <livewire:date-range-picker wire:model="dateRange" />

        <div class="flex">
            <flux:spacer />

            <flux:button wire:click="save" variant="primary">Modifier</flux:button>
        </div>
    </div>
</flux:modal>

@push('scripts')
    <script>
        function travelUpdateCleanup() {
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
@endpush
