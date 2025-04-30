@props(['activity'])

<div class="flex flex-col p-4 bg-primary-500 rounded-md shadow-md">
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
        <a href="{{ $activity->url }}" target="_blank" class="text-zinc-600 dark:text-zinc-300 underline w-min whitespace-nowrap">
            Voir le lien
        </a>
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

    <flux:spacer />

    <div class="flex mt-auto">
        <flux:spacer />
            <flux:icon.pencil-square class="cursor-pointer" wire:click="selectActivity('{{ $activity->id }}')" />
    </div>
</div>
