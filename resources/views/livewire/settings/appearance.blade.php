<section class="w-full" role="main" aria-labelledby="appearance-heading">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <flux:radio.group
            x-data variant="segmented"
            x-model="$flux.appearance"
            role="radiogroup"
            aria-labelledby="appearance-heading"
            aria-label="Sélection du thème d'apparence"
            description="Choisissez le thème d'apparence qui vous convient le mieux. Le thème système s'adapte automatiquement à vos préférences système.">
            <flux:radio value="light" icon="sun" aria-label="Thème clair">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon" aria-label="Thème sombre">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop" aria-label="Thème système">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
