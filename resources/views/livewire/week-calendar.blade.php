<div class="flex-1 flex flex-col items-start min-h-0 w-full">
    <header class="flex justify-between items-center h-min w-full">
        <div class="text-sm">
            {{ $startDateString }} - {{ $endDateString }}
        </div>
        <div class="flex items-center space-x-2">
            <div wire:click="previous"
                class="cursor-pointer text-xl sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800"
                aria-label="Previous week" role="button" tabindex="0">
                &lt;
            </div>
            <div wire:click="next"
                class="cursor-pointer text-xl sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800"
                aria-label="Next week" role="button" tabindex="0">
                &gt;
            </div>
        </div>
    </header>

    <!-- Day headers -->
    <div class="grid grid-cols-7 gap-px text-center text-xs font-medium text-zinc-500 h-min w-full mt-2">
        @foreach ($days as $day)
            <div class="py-2 flex flex-col {{ $day['isToday'] ? 'bg-primary-50 font-bold' : '' }}">
                <span>{{ $day['shortDayName'] }}</span>
                <span>{{ $day['day'] }}/{{ $day['month'] }}</span>
            </div>
        @endforeach
    </div>

    <!-- Week view as columns -->
    <div class="h-full w-full grid grid-cols-7 min-h-0 border-t border-primary-500">
        @foreach ($days as $day)
            <div class="flex flex-col p-1 h-full border-r border-primary-500 min-h-0 {{ $day['isToday'] ? 'bg-primary-50' : '' }}">
                <div class="flex-1 overflow-y-auto min-h-0 gap-1 flex flex-col mt-1">
                    @foreach ($day['events'] as $event)
                        <div class="text-xs border-l-2 border-secondary-400 bg-secondary-50 px-2 py-1 rounded mb-1">
                            <div class="font-medium">{{ $event['name'] }}</div>
                            <div class="text-zinc-500">{{ $event['start_time'] }}{{ isset($event['end_time']) ? ' - ' . $event['end_time'] : '' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
