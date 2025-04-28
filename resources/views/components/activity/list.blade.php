@props(['activities' => [], 'title'])

<div class="flex flex-col gap-6 p-6 bg-zinc-50 dark:bg-zinc-900 rounded-2xl shadow-sm">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white pl-4">
        {{ $title }}
    </flux:heading>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($activities as $activity)
            <x-activity.card :activity="$activity" />
        @empty
            <p class="ml-4 text-zinc-500 dark:text-zinc-400">Vous n'avez aucune activité pour le moment.</p>
        @endforelse
    </div>
</div>
