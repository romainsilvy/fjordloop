@props(['travel'])

<p class="text-zinc-500 dark:text-zinc-400 text-sm">
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
