<section class="w-full" role="main" aria-labelledby="profile-heading">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6" role="form" aria-labelledby="profile-heading">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" aria-required="true" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" aria-required="true" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div role="alert" aria-live="polite">
                        <flux:text class="mt-4">
                            {{ __('auth.verification.unverified') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification" aria-label="Renvoyer l'email de vérification">
                                {{ __('auth.verification.resend_link') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !text-green-600" role="status" aria-live="polite">
                                {{ __('auth.verification.link_sent') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" aria-label="Sauvegarder le profil">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
