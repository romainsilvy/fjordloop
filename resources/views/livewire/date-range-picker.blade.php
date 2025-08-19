<flux:field>
    <flux:label class="inline-flex items-center text-sm font-medium text-zinc-800 ">
        Dates
    </flux:label>

    <div
        class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm py-2 min-h-10 leading-[1.375rem] ps-3 pe-3 bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200"
        role="application"
        aria-label="Sélecteur de plage de dates">

        <header class="flex justify-between items-center" role="banner">
            <div class=" text-sm">
                {{ $monthName }} {{ $currentYear }}
            </div>

            <div class="flex items-center" role="toolbar" aria-label="Navigation du calendrier">
                <div wire:click="previous"
                    class="cursor-pointer text-xl sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800"
                    aria-label="Mois précédent" role="button" tabindex="0">
                    &lt;
                </div>

                <div wire:click="next"
                    class="cursor-pointer text-xl sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800"
                    aria-label="Mois suivant" role="button" tabindex="0">
                    &gt;
                </div>
            </div>
        </header>

        <table class="w-full" role="grid">
            <thead>
                <tr class="flex w-full" role="row">
                    @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $shortDayName)
                    <th class="w-1/7 text-center text-sm font-medium text-zinc-500 py-2" role="columnheader" aria-label="{{ $shortDayName }}">
                            <div class="w-full">{{ $shortDayName }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($days as $weekIndex => $weekDays)
                    <tr class="flex w-full not-first-of-type:mt-1" role="row">
                        @foreach ($weekDays as $day)
                            <td wire:click="selectDate({{ $day['day'] }}, '{{ $day['month'] }}', {{ $day['year'] }})"
                                class="w-1/7 p-0 {{ $day['isBetween'] || $day['isSelectedStart'] || $day['isSelectedEnd'] ? 'bg-zinc-100' : '' }}"
                                role="gridcell"
                                aria-label="Jour {{ $day['day'] }} {{ $day['month'] != $currentMonth ? 'du mois précédent' : '' }} {{ $day['isToday'] ? 'aujourd\'hui' : '' }}">
                                <ui-tooltip position="top">
                                    <button type="button"
                                        class="w-full size-11 text-sm font-medium flex flex-col items-center justify-center rounded-lg
                                       {{ ($day['isSelectedStart'] || $day['isSelectedEnd']) ? 'bg-accent !text-accent-foreground' : 'hover:bg-zinc-800/5' }}
                                       {{ $day['month'] != $currentMonth ? 'text-zinc-400' : 'text-zinc-800 ' }}"
                                        aria-label="Sélectionner le {{ $day['day'] }} {{ $day['month'] != $currentMonth ? 'du mois précédent' : '' }} {{ $day['isToday'] ? 'aujourd\'hui' : '' }}">
                                        <div class="relative w-4">
                                            @if ($day['isToday'])
                                                <div
                                                    class="absolute inset-x-0 bottom-[-3px] flex justify-center items-center"
                                                    aria-hidden="true">
                                                    <div class="size-1 rounded-full bg-zinc-800 m-auto">
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
