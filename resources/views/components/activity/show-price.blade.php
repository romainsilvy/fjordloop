@props(['activity'])

@if (isset($activity->price_by_person) || isset($activity->price_by_group))
    <div class="flex flex-row items-center justify-start gap-2">
        <flux:icon.banknotes class="size-4" />
        @if (isset($activity->price_by_person))
            <p>
                @euro($activity->price_by_person) / personne
            </p>
        @elseif (isset($activity->price_by_group))
            <p>
                @euro($activity->price_by_group) / groupe
            </p>
        @endif
    </div>
@endif
