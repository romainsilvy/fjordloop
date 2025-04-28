@props(['activities' => [], 'title'])

<div class="flex flex-col gap-6 p-6 bg-zinc-50 dark:bg-zinc-900 rounded-2xl shadow-sm">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white pl-4">
        {{ $title }}
    </flux:heading>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($activities as $activity)
            <x-activity.card :activity="$activity" />
        @endforeach
    </div>
</div>
