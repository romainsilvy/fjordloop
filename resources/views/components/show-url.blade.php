@props(['url' => null])

@if ($url)
    <div class="flex flex-row items-center justify-start gap-2">
        <flux:icon.link class="size-4" />

        <p class="underline">
            {{ \Illuminate\Support\Str::limit($url, 50) }}
        </p>
    </div>
@endif
