<div class="flex-1 flex flex-col items-start min-h-0 w-full bg-white rounded-lg shadow">
    <!-- Calendar Header -->
    <header class="flex justify-between items-center w-full p-4 border-b">
        <div class="text-lg font-semibold text-zinc-800">
            {{ $monthName }} {{ $currentYear }}
        </div>
        <div class="flex items-center space-x-2">
            <button wire:click="previous"
                class="cursor-pointer size-8 rounded-full flex items-center justify-center text-zinc-600 hover:bg-zinc-100"
                aria-label="Previous month" role="button" tabindex="0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button wire:click="next"
                class="cursor-pointer size-8 rounded-full flex items-center justify-center text-zinc-600 hover:bg-zinc-100"
                aria-label="Next month" role="button" tabindex="0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Day Names -->
    <div class="grid grid-cols-7 w-full border-b">
        @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $shortDayName)
            <div class="py-2 text-center text-xs font-medium text-zinc-500">
                {{ $shortDayName }}
            </div>
        @endforeach
    </div>

    <!-- Calendar Grid -->
    <div class="h-full w-full flex flex-col min-h-0 flex-1">
        @foreach ($days as $weekDays)
            <div class="grid grid-cols-7 w-full flex-1 min-h-0">
                @foreach ($weekDays as $day)
                    <!-- Day Cell -->
                    <div
                        class="flex flex-col h-full border-b border-r border-zinc-100 min-h-0 relative
                            @if($day['month'] != $currentMonth)
                                bg-zinc-50 text-zinc-400
                            @endif
                            @if($day['isToday'])
                                bg-primary-50
                            @endif"
                    >
                        <!-- Day Number -->
                        <div class="p-1 text-xs font-medium flex justify-between items-center">
                            <div
                                class="@if($day['isToday']) bg-primary-500 text-black size-6 rounded-full flex items-center justify-center @endif"
                            >
                                {{ $day['day'] }}
                            </div>

                            @if($day['month'] == $currentMonth && count($day['events']) > 0)
                                <div class="text-xs text-secondary-500 font-medium">
                                    {{ count($day['events']) }} {{ count($day['events']) == 1 ? 'évènement' : 'évènements' }}
                                </div>
                            @endif
                        </div>

                        <!-- Events -->
                        <div class="flex-1 overflow-y-auto min-h-0 flex flex-col gap-1 p-1">
                            @foreach ($day['events'] as $event)
                                <div class="text-xs bg-secondary-50 border-l-2 border-secondary-500 px-2 py-1 rounded shadow-sm {{ $event['latitude'] && $event['longitude'] ? 'cursor-pointer' : '' }} " @if ($event['latitude'] && $event['longitude'])
                                    @click="$dispatch('focus-map-marker', { latitude: {{ $event['latitude'] }}, longitude: {{ $event['longitude'] }} } )"
                                @endif>
                                    <div class="text-secondary-600 font-medium">{{ $event['name'] }}</div>
                                    <div class="text-zinc-500">{{ $event['start_time'] }} {{ $event['end_time'] ? ' - ' : ''}} {{ $event['end_time'] }}</div>
                                    <div class="text-zinc-500 text-xs">{{ $event['place_name'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
