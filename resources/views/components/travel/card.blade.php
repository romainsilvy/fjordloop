<a class="p-4 bg-primary-500 rounded-md shadow-md space-y-2"
    href="{{ route('travel.show', ['travelId' => $travel->id]) }}" wire:navigate>
    <h2 class="text-lg font-semibold text-zinc-800 dark:text-white">
        {{ $travel->name }}
    </h2>

    <x-travel.show-date :travel="$travel" />

    <p class="text-zinc-600 dark:text-zinc-300">
        {{ $travel->place_name }}
    </p>
</a>
