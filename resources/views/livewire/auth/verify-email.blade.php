<div class="mt-4 flex flex-col gap-6" role="main" aria-labelledby="verify-email-title">
    <flux:text class="text-center" id="verify-email-title">
        {{ __('auth.verification.verify_message') }}
    </flux:text>

    @if (session('status') == 'verification-link-sent')
        <flux:text class="text-center font-medium !text-green-600" role="status" aria-live="polite">
            {{ __('auth.verification.registration_link_sent') }}
        </flux:text>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3" role="group" aria-label="Actions de vérification">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full" aria-label="Renvoyer l'email de vérification">
            {{ __('auth.verification.resend_email') }}
        </flux:button>

        <flux:link class="text-sm cursor-pointer" wire:click="logout" aria-label="Se déconnecter">
            {{ __('auth.verification.logout') }}
        </flux:link>
    </div>
</div>
