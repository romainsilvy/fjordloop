@props(['activity'])

<a class="p-4 bg-primary-500 rounded-md shadow-md space-y-2" {{-- href="{{ route('travel.show', ['travelId' => $travel->id]) }}" wire:navigate --}} href="#">
    <h2 class="text-lg font-semibold text-zinc-800 dark:text-white">
        {{ $activity->name }}
    </h2>

    <p class="text-zinc-600 dark:text-zinc-300">
        {{ $activity->description }}
    </p>


    <p class="text-zinc-600 dark:text-zinc-300">
        {{ $activity->place_name }}
    </p>

    @if ($activity->url)
        <p x-on:click="window.open('{{ $activity->url }}', '_blank')" class="text-zinc-600 dark:text-zinc-300 underline">
            Voir le lien
        </p>
    @endif

    @if (isset($activity->price_by_person))
        <p class="text-zinc-600 dark:text-zinc-300">
            Prix par personne : @euro($activity->price_by_person)
        </p>
    @elseif (isset($activity->price_by_group))
        <p class="text-zinc-600 dark:text-zinc-300">
            Prix total : @euro($activity->price_by_group)
        </p>
    @endif
</a>
