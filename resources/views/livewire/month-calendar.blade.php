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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button wire:click="next"
                class="cursor-pointer size-8 rounded-full flex items-center justify-center text-zinc-600 hover:bg-zinc-100"
                aria-label="Next month" role="button" tabindex="0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5">
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

    <!-- ░░  CALENDAR GRID  ░░ -->
<div class="w-full flex flex-col min-h-0 flex-1">
    @foreach ($days as $weekIndex => $weekDays)
        @php
            $rowLevels   = $housingBars[$weekIndex] ?? [];           // stacked bars
            $levelCount  = count($rowLevels);                        // how many rows of bars
            $barHeightPx = 20;                                       // h-5  →  20 px
            $headerGapPx = 22;                                       // top-8 → 32 px (room under day-header)
            $barSpace = 4;                                       // top-8 → 32 px (room under day-header)

        @endphp

        <div class="relative w-full min-h-0">
            {{-- ① DAY-CELL GRID  (fixed height) --}}
            <div class="grid grid-cols-7 w-full">
                @foreach ($weekDays as $day)
                    @php
                        $dayEvents = collect($day['events'])
                            ->reject(fn($e) => $e['type'] === 'housing');   // bars already visualise them
                    @endphp

                    <div class="h-40 flex flex-col border-b border-r border-zinc-100 relative
                                {{ $day['month'] != $currentMonth ? 'bg-zinc-50 text-zinc-400' : '' }}
                                {{ $day['isToday'] ? 'bg-primary-50' : '' }}">
                        {{-- Day header --}}
                        <div class="p-1 text-xs font-medium flex justify-between items-center">
                            <div class="{{ $day['isToday'] ? 'bg-primary-500 text-black size-6 rounded-full flex items-center justify-center' : '' }}">
                                {{ $day['day'] }}
                            </div>
                            @if ($day['month'] == $currentMonth && $dayEvents->count())
                                <div class="text-xs text-secondary-500 font-medium">
                                    {{ $dayEvents->count() }}
                                    {{ $dayEvents->count() == 1 ? 'évènement' : 'évènements' }}
                                </div>
                            @endif
                        </div>

                        {{-- scrollable activities, pushed down by total bar height --}}
                        <div class="flex-1 overflow-y-auto min-h-0 flex flex-col gap-1 p-1"
                             style="margin-top: {{ $levelCount * ($barHeightPx + $barSpace) - $barSpace }}px;">
                            @foreach ($dayEvents as $event)
                                <div class="text-xs border-l-2 px-2 py-1 rounded shadow-sm
                                            {{ $event['latitude'] && $event['longitude'] ? 'cursor-pointer' : '' }}
                                            bg-tertiary-50 border-tertiary-500"
                                     @if ($event['latitude'] && $event['longitude'])
                                         @click="$dispatch('focus-map-marker', { latitude: {{ $event['latitude'] }}, longitude: {{ $event['longitude'] }} })"
                                     @endif>
                                    <div class="font-medium text-tertiary-600 whitespace-nowrap overflow-hidden text-ellipsis"><span class="text-black">{{ $event['start_time'] }} - </span>{{ $event['name'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>


            {{-- ② STACKED HOUSING BARS  (absolute overlays) --}}
            @foreach ($rowLevels as $levelIdx => $levelBars)
                <div class="absolute inset-x-0"
                     style="top: {{ $headerGapPx + ($levelIdx * ($barHeightPx + $barSpace)) }}px; height: {{ $barHeightPx }}px;
                            display: grid; grid-template-columns: repeat(7,minmax(0,1fr)); gap: 1px;">
                    @foreach ($levelBars as $bar)
                        <div class="bg-secondary-200 text-secondary-900
                                    text-[10px] leading-tight rounded px-1 overflow-hidden cursor-pointer"
                             style="grid-column: {{ $bar['colStart'] }} / span {{ $bar['span'] }};"
                             @click="$dispatch('focus-map-marker', { latitude: {{ $bar['latitude'] }}, longitude: {{ $bar['longitude'] }} })">
                            <span class="font-medium">{{ $bar['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach
</div>

</div>
