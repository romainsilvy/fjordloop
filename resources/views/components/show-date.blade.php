@props(['startDate' => null, 'endDate' => null, 'startTime' => null, 'endTime' => null])

@if ($startDate)
    <div class="flex flex-row items-center justify-start gap-2">
        <flux:icon.calendar-date-range class="size-4" />

        <p>
            @if ($startDate)
                @if (!$endDate || $startDate->isSameDay($endDate))
                    Le {{ $startDate->format('d/m/Y') }}

                    @if ($startTime && $endTime)
                        de {{ $startTime }} à {{ $endTime }}
                    @elseif ($startTime)
                        à {{ $startTime }}
                    @endif
                @else
                    Du {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}
                    @if ($startTime && $endTime)
                        de {{ $startTime }} à {{ $endTime }}
                    @elseif ($startTime)
                        à {{ $startTime }}
                    @endif
                @endif
            @endif

        </p>
    </div>
@endif
