<div class="flex flex-col gap-6" role="main" aria-labelledby="confirm-password-title">
    <x-auth-header
        :title="__('Confirm password')"
        :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
    />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6" role="form" aria-labelledby="confirm-password-title">
        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            aria-required="true"
            description="Entrez votre mot de passe actuel pour confirmer votre identité et accéder à cette zone sécurisée"
        />

        <flux:button variant="primary" type="submit" class="w-full" aria-label="Confirmer le mot de passe">{{ __('Confirm') }}</flux:button>
    </form>
</div>
