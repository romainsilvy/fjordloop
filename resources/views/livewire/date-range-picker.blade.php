<flux:field>
    <flux:label class="inline-flex items-center text-sm font-medium text-zinc-800 dark:text-white">
        Dates
    </flux:label>

    <div
        class="w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm py-2 min-h-10 leading-[1.375rem] ps-3 pe-3 bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5">

        <header class="flex justify-between items-center">

            <div class="dark:text-white text-sm">
                {{ $monthName }} {{ $currentYear }}
            </div>

            <div class="flex items-center">
                <div wire:click="previous"
                    class="cursor-pointer text-xl sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-white/5 dark:hover:text-white "
                    aria-label="Previous month" role="button" tabindex="0">
                    &lt;
                </div>

                <div wire:click="next"
                    class="cursor-pointer text-xl sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-white/5 dark:hover:text-white "
                    aria-label="Next month" role="button" tabindex="0">
                    &gt;
                </div>
            </div>
        </header>



        <table class="w-full">
            <thead>
                <tr class="flex w-full">

                    @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $shortDayName)
                    <th class="w-1/7 text-center text-sm font-medium text-zinc-500 dark:text-zinc-300 py-2">
                            <div class="w-full">{{ $shortDayName }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($days as $weekDays)
                    <tr class="flex w-full not-first-of-type:mt-1">
                        @foreach ($weekDays as $day)
                            <td wire:click="selectDate({{ $day['day'] }}, '{{ $day['month'] }}', {{ $day['year'] }})"
                                class="w-1/7 p-0 {{ $day['isBetween'] || $day['isSelectedStart'] || $day['isSelectedEnd'] ? 'bg-zinc-100 dark:bg-white/10' : '' }}">
                                <ui-tooltip position="top">
                                    <button type="button"
                                        class="w-full size-11 text-sm font-medium flex flex-col items-center justify-center rounded-lg
                                       {{ ($day['isSelectedStart'] || $day['isSelectedEnd']) ? 'bg-accent !text-accent-foreground' : 'hover:bg-zinc-800/5 dark:hover:bg-white/5' }}
                                       {{ $day['month'] != $currentMonth ? 'text-zinc-400' : 'text-zinc-800 dark:text-white' }}">
                                        <div class="relative w-4">
                                            @if ($day['isToday'])
                                                <div
                                                    class="absolute inset-x-0 bottom-[-3px] flex justify-center items-center">
                                                    <div class="size-1 rounded-full bg-zinc-800 dark:bg-white m-auto">
                                                    </div>
                                                </div>
                                            @endif

                                            <div>
                                                {{ $day['day'] }}
                                            </div>
                                        </div>
                                    </button>
                                </ui-tooltip>
                            </td>
                        @endforeach

                    </tr>
                @endforeach


            </tbody>
        </table>
    </div>
</flux:field>
