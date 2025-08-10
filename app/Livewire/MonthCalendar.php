<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Travel;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class MonthCalendar extends Component
{
    public int $currentYear;

    public int $currentMonth;

    public int $previousYear;

    public int $previousMonth;

    public int $nextYear;

    public int $nextMonth;

    public int $daysInMonth;

    public string $monthName;
    public $events = [];

    public Travel $travel;


    // Full calendar split into weeks (each week is a list of day arrays)
    /**
     * @var list<list<array{
     *   day:int,
     *   month:int,
     *   year:int,
     *   isToday:bool,
     *   events:mixed
     * }>>
     */
    public array $days = [];
    /** @var array<int,array<int,array<string,mixed>>>                       */
    public array $housingBars = [];   // grouped by week index


    public function mount(Travel $travel): void
    {
        $this->travel = $travel;

        $today = Carbon::today();
        $travelStart = $travel->start_date ?? $today;
        $travelEnd = $travel->end_date ?? $travelStart;

        $startDate = $today->between($travelStart, $travelEnd)
            ? $today
            : $travelStart;

        $this->updateStoredDates($startDate);
        $this->updateCalendar();
    }

    #[On('activityCreated')]
    #[On('housingCreated')]
    public function updateCalendar(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1);
        if ($date) {
            $this->daysInMonth = $date->daysInMonth;
            $this->monthName = ucfirst($date->translatedFormat('F'));

            $firstDay = $date->dayOfWeekIso;
            $previousMonth = $date->copy()->subMonth();
            $daysInPreviousMonth = $previousMonth->daysInMonth;

            /** @var list<array{day:int,month:int,year:int,isToday:bool,events:mixed}> $daysFlat */
            $daysFlat = [];

            // prev-month padding
            for ($i = $firstDay - 1; $i > 0; $i--) {
                $day = $daysInPreviousMonth - $i + 1;
                $currentDate = Carbon::create($this->previousYear, $this->previousMonth, $day);
                if ($currentDate) {
                    $daysFlat[] = [
                        'day' => $day,
                        'month' => $this->previousMonth,
                        'year' => $this->previousYear,
                        'isToday' => $currentDate->isToday(),
                        'events' => $this->travel->getDayEvents($currentDate),
                    ];
                }
            }

            // current month
            for ($i = 1; $i <= $this->daysInMonth; $i++) {
                $currentDate = Carbon::create($this->currentYear, $this->currentMonth, $i);
                if ($currentDate) {
                    $daysFlat[] = [
                        'day' => $i,
                        'month' => $this->currentMonth,
                        'year' => $this->currentYear,
                        'isToday' => $currentDate->isToday(),
                        'events' => $this->travel->getDayEvents($currentDate),
                    ];
                }
            }

            // next-month padding
            $remainingCells = 7 - (count($daysFlat) % 7);
            $remainingCells = $remainingCells === 7 ? 0 : $remainingCells;

            for ($i = 1; $i <= $remainingCells; $i++) {
                $currentDate = Carbon::create($this->nextYear, $this->nextMonth, $i);
                if ($currentDate) {
                    $daysFlat[] = [
                        'day' => $i,
                        'month' => $this->nextMonth,
                        'year' => $this->nextYear,
                        'isToday' => $currentDate->isToday(),
                        'events' => $this->travel->getDayEvents($currentDate),
                    ];
                }
            }

            // Now safely assign the chunked weeks to the property
            /** @var list<list<array{day:int,month:int,year:int,isToday:bool,events:mixed}>> $weeks */
            $weeks = array_chunk($daysFlat, 7);
            $this->days = $weeks;
        }

        /* -----------------------------------------------------------------
     | Build stacked housing bars — $housingBars[$weekIdx][$level][]   |
     +----------------------------------------------------------------*/
        $this->housingBars = [];
        $housings = $this->travel->housings()->get();

        foreach ($this->days as $weekIdx => $weekDays) {
            /** @var array{
             *   0: array{day:int,month:int,year:int,isToday:bool,events:mixed},
             *   6: array{day:int,month:int,year:int,isToday:bool,events:mixed}
             * } $weekDays
             */

            $rowStart = Carbon::create(
                $weekDays[0]['year'],
                $weekDays[0]['month'],
                $weekDays[0]['day']
            )->startOfDay();

            $rowEnd = Carbon::create(
                $weekDays[6]['year'],
                $weekDays[6]['month'],
                $weekDays[6]['day']
            )->endOfDay();

            foreach ($housings as $h) {
                if ($h->end_date->lt($rowStart) || $h->start_date->gt($rowEnd)) {
                    continue;
                }

                $colStart = max(1, $h->start_date->lt($rowStart) ? 1 : $h->start_date->dayOfWeekIso);
                $colEnd   = min(7, $h->end_date->gt($rowEnd) ? 7 : $h->end_date->dayOfWeekIso);
                $span     = $colEnd - $colStart + 1;

                $bar = [
                    'name'      => $h->name,
                    'place'     => $h->place_name,
                    'colStart'  => $colStart,
                    'span'      => $span,
                    'latitude'  => $h->place_latitude,
                    'longitude' => $h->place_longitude,
                ];

                $placed = false;
                foreach ($this->housingBars[$weekIdx] ?? [] as $level => $barsAtLevel) {
                    $collision = collect($barsAtLevel)->contains(function ($b) use ($colStart, $colEnd) {
                        $bStart = $b['colStart'];
                        $bEnd   = $bStart + $b['span'] - 1;
                        return max($bStart, $colStart) <= min($bEnd, $colEnd);
                    });

                    if (!$collision) {
                        $this->housingBars[$weekIdx][$level][] = $bar;
                        $placed = true;
                        break;
                    }
                }

                if (!$placed) {
                    $this->housingBars[$weekIdx][] = [$bar];
                }
            }
        }
    }

    public function next(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1);
        if ($date) {
            $date->addMonth();
            $this->updateStoredDates($date);
            $this->updateCalendar();
        }
    }

    public function previous(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1);
        if ($date) {
            $date->subMonth();
            $this->updateStoredDates($date);
            $this->updateCalendar();
        }
    }

    public function updateStoredDates(Carbon $date): void
    {
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->previousMonth = $date->copy()->subMonth()->month;
        $this->previousYear = $date->copy()->subMonth()->year;
        $this->nextMonth = $date->copy()->addMonth()->month;
        $this->nextYear = $date->copy()->addMonth()->year;
    }

    public function render()
    {
        return view('livewire.month-calendar');
    }
}
