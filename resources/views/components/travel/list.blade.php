@props(['travels' => [], 'title'])

<div class="flex flex-col gap-6">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white pl-4">
        {{ $title }}
    </flux:heading>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($travels as $travel)
            <x-travel.card :travel="$travel" />
        @empty
            <p class="ml-4 text-black">Vous n'avez aucun voyage pour le moment.</p>
        @endforelse
    </div>
</div>
