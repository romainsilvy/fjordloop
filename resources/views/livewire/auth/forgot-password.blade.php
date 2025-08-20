<div class="flex flex-col gap-6" role="main" aria-labelledby="forgot-password-title">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6" role="form" aria-labelledby="forgot-password-title">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email Address')"
            type="email"
            required
            autofocus
            placeholder="email@example.com"
            aria-required="true"
            description="Entrez l'adresse e-mail associée à votre compte pour recevoir un lien de réinitialisation"
        />

        <flux:button variant="primary" type="submit" class="w-full" aria-label="Envoyer le lien de réinitialisation du mot de passe">{{ __('Email password reset link') }}</flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400" role="complementary" aria-label="Lien vers la connexion">
        {{ __('Or, return to') }}
        <flux:link :href="route('login')" wire:navigate aria-label="Se connecter">{{ __('log in') }}</flux:link>
    </div>
</div>
