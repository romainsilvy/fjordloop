@props(['activity'])

@if ($activity->start_date)

<div class="flex flex-row items-center justify-start gap-2">
    <flux:icon.calendar-date-range class="size-4" />

    <p>
        @if ($activity->start_date)
            @if (!$activity->end_date || $activity->start_date->isSameDay($activity->end_date))
                Le {{ $activity->start_date->format('d/m/Y') }}

                @if ($activity->start_time && $activity->end_time)
                    de {{ $activity->start_time }} à {{ $activity->end_time }}
                @elseif ($activity->start_time)
                    à {{ $activity->start_time }}
                @endif
            @else
                Du {{ $activity->start_date->format('d/m/Y') }} au {{ $activity->end_date->format('d/m/Y') }}
                @if ($activity->start_time && $activity->end_time)
                    de {{ $activity->start_time }} à {{ $activity->end_time }}
                @elseif ($activity->start_time)
                    à {{ $activity->start_time }}
                @endif
            @endif
        @endif

    </p>
</div>
@endif
