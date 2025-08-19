<div class="mt-4 flex flex-col gap-6">
    <flux:text class="text-center">
        {{ __('auth.verification.verify_message') }}
    </flux:text>

    @if (session('status') == 'verification-link-sent')
        <flux:text class="text-center font-medium !text-green-600">
            {{ __('auth.verification.registration_link_sent') }}
        </flux:text>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('auth.verification.resend_email') }}
        </flux:button>

        <flux:link class="text-sm cursor-pointer" wire:click="logout">
            {{ __('auth.verification.logout') }}
        </flux:link>
    </div>
</div>
