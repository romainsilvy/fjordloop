<div class="flex-1 flex flex-col items-start min-h-0 w-full">
    <header class="flex justify-between items-center h-min w-full">
        <div class="text-sm">
            {{ $monthName }} {{ $currentYear }}
        </div>

        <div class="flex items-center space-x-2">
            <div wire:click="previous"
                 class="cursor-pointer text-xl sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800"
                 aria-label="Previous month" role="button" tabindex="0">
                &lt;
            </div>

            <div wire:click="next"
                 class="cursor-pointer text-xl sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800"
                 aria-label="Next month" role="button" tabindex="0">
                &gt;
            </div>
        </div>
    </header>

    <div class="grid grid-cols-7 gap-px text-center text-xs font-medium text-zinc-500 h-min w-full">
        @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $shortDayName)
            <div class="py-2">{{ $shortDayName }}</div>
        @endforeach
    </div>

    <div class="h-full w-full flex flex-col min-h-0">
        @foreach ($days as $weekDays)
            <div class="grid grid-cols-7 w-full flex-1 min-h-0">
                @foreach ($weekDays as $day)
                    <div class="flex flex-col p-0 h-full border border-primary-500 min-h-0">
                        <div class="text-xs mx-auto w-min shrink-0">{{ $day['day'] }}</div>
                        <div class="flex-1 overflow-y-auto min-h-0 gap-1 flex flex-col mt-1">
                            @foreach ($day['events'] as $event)
                                <div class="text-xs border border-secondary-400 px-1 rounded mx-1">{{ $event['start_time'] }} {{ $event['name'] }}</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
