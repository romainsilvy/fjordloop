<a class="p-4 bg-primary-500 rounded-md shadow-md space-y-2"
    href="{{ route('travel.show', ['travelId' => $travel->id]) }}" wire:navigate>
    <h2 class="text-lg font-semibold text-zinc-800 dark:text-white">
        {{ $travel->name }}
    </h2>

    <x-travel.show-date :travel="$travel" />

    @if ($travel->place_name)
        <div class="flex flex-row items-center justify-start gap-2">
            <flux:icon.map-pin class="size-4" />
            <p class="text-sm">{{ $travel->place_name }}
        </div>
    @endif

</a>
