@props(['travel'])

<div class="flex flex-row items-center justify-start gap-2">
    <flux:icon.calendar-date-range class="size-4"/>

    <p class="text-sm">
        @if (isset($travel->start_date) && isset($travel->end_date))
        Du
        {{ $travel->start_date->format('d/m/Y') }}
        au
        {{ $travel->end_date->format('d/m/Y') }}
        @elseif (isset($travel->start_date))
        Départ le
        {{ $travel->start_date->format('d/m/Y') }}
        @elseif (isset($travel->end_date))
        Retour le
        {{ $travel->end_date->format('d/m/Y') }}
        @else
        Pas de dates définies
        @endif
    </p>
</div>
