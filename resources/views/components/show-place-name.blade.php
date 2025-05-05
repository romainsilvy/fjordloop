@props(['placeName' => null])

@if ($placeName)
    <div class="flex flex-row items-center justify-start gap-2">
        <flux:icon.map-pin class="size-4" />

        <p>{{ $placeName }}</p>
    </div>
@endif
