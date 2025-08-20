@props(['placeName' => null])

@if ($placeName)
    <div class="flex flex-row items-center justify-start gap-2" role="group" aria-label="Informations de localisation">
        <flux:icon.map-pin class="size-4" aria-hidden="true" />

        <p>{{ $placeName }}</p>
    </div>
@endif
