<section class="mt-10 space-y-6" role="region" aria-labelledby="delete-account-heading">
    <div class="relative mb-5">
        <flux:heading id="delete-account-heading">{{ __('Delete account') }}</flux:heading>
        <flux:subheading>{{ __('Delete your account and all of its resources') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" aria-label="Supprimer le compte utilisateur">
            {{ __('Delete account') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg" role="dialog" aria-labelledby="confirm-deletion-title" aria-describedby="confirm-deletion-description">
        <form wire:submit="deleteUser" class="space-y-6" role="form" aria-labelledby="confirm-deletion-title">
            <div>
                <flux:heading size="lg" id="confirm-deletion-title">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

                <flux:subheading id="confirm-deletion-description">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </flux:subheading>
            </div>

            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                aria-required="true"
                description="Entrez votre mot de passe actuel pour confirmer la suppression définitive de votre compte" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled" aria-label="Annuler la suppression">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit" aria-label="Confirmer la suppression du compte">{{ __('Delete account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
