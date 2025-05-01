@props(['activity'])

@if ($activity->place_name)
    <div class="flex flex-row items-center justify-start gap-2">
        <flux:icon.map-pin class="size-4" />

        <p>{{ $activity->place_name }}</p>
    </div>
@endif
