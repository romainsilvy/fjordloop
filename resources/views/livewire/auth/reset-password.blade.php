<div class="flex flex-col gap-6" role="main" aria-labelledby="reset-password-title">
    <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="resetPassword" class="flex flex-col gap-6" role="form" aria-labelledby="reset-password-title">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email')"
            type="email"
            required
            autocomplete="email"
            aria-required="true"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            aria-required="true"
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            aria-required="true"
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full" aria-label="Réinitialiser le mot de passe">
                {{ __('Reset password') }}
            </flux:button>
        </div>
    </form>
</div>
