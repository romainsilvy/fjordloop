@props(['url' => null])

@if ($url)
    <div class="flex flex-row items-center justify-start gap-2">
        <flux:icon.link class="size-4" />

        <p class="underline">
            {{ $url }}
        </p>
    </div>
@endif
