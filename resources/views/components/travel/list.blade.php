@props(['travels' => [], 'title'])

<div class="flex flex-col gap-6 p-6 bg-zinc-50 dark:bg-zinc-900 rounded-2xl shadow-sm">
    <flux:heading size="lg" class="text-zinc-800 dark:text-white pl-4">
        {{ $title }}
    </flux:heading>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($travels as $travel)
            <x-travel.card :travel="$travel" />
        @endforeach
    </div>
</div>
