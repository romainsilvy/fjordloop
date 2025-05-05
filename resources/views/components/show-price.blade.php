@props(['priceByPerson' => null, 'priceByGroup' => null])

@if (isset($priceByPerson) || isset($priceByGroup))
    <div class="flex flex-row items-center justify-start gap-2">
        <flux:icon.banknotes class="size-4" />
        @if (isset($priceByPerson))
            <p>
                @euro($priceByPerson) / personne
            </p>
        @elseif (isset($priceByGroup))
            <p>
                @euro($priceByGroup) / groupe
            </p>
        @endif
    </div>
@endif
