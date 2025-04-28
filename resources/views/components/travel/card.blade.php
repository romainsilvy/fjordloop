<a class="p-4 bg-white dark:bg-zinc-800 rounded-2xl shadow-md space-y-2"
    href="{{ route('travel.show', ['travelId' => $travel->id]) }}" wire:navigate>
    <h2 class="text-lg font-semibold text-zinc-800 dark:text-white">
        {{ $travel->name }}
    </h2>

    @if (isset($travel->start_date) && isset($travel->end_date))
        <p class="text-zinc-500 dark:text-zinc-400 text-sm">
            Du
            {{ $travel->start_date->format('d/m/Y') }}
            au
            {{ $travel->end_date->format('d/m/Y') }}
        </p>
    @elseif (isset($travel->start_date))
        <p class="text-zinc-500 dark:text-zinc-400 text-sm">
            Départ le
            {{ $travel->start_date->format('d/m/Y') }}
        </p>
    @elseif (isset($travel->end_date))
        <p class="text-zinc-500 dark:text-zinc-400 text-sm">
            Retour le
            {{ $travel->end_date->format('d/m/Y') }}
        </p>
    @else
        <p class="text-zinc-500 dark:text-zinc-400 text-sm">
            Pas de dates définies
        </p>
    @endif

    <p class="text-zinc-600 dark:text-zinc-300">
        {{ $travel->place_name }}
    </p>
</a>
