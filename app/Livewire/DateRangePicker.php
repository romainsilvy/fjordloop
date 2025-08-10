<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Modelable;

class DateRangePicker extends Component
{
    public int $currentYear;

    public int $currentMonth;

    public int $previousYear;

    public int $previousMonth;

    public int $nextYear;

    public int $nextMonth;

    public int $daysInMonth;

    public string $monthName;

    /**
     * @var array{start: string, end: string}
     */
    #[Modelable]
    public array $dateRange = [
        'start' => '',
        'end'   => '',
    ];

    /**
     * Full calendar split into weeks (each week is a list of day arrays)
     * @var list<list<array{
     *   day:int,
     *   month:int,
     *   year:int,
     *   isSelectedStart:bool,
     *   isSelectedEnd:bool,
     *   isBetween:bool,
     *   isToday:bool
     * }>>
     */
    public array $days = [];

    public function mount(): void
    {
        // ensure keys exist (helps PHPStan and removes “non-empty-string” headaches)
        $this->dateRange = $this->dateRange + ['start' => '', 'end' => ''];

        $this->updateStoredDates(Carbon::now());
        $this->updateCalendar();
    }

    public function updateCalendar(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1);
        if ($date) {
            $this->daysInMonth = $date->daysInMonth;
            $this->monthName = ucfirst($date->translatedFormat('F'));

            $firstDay = $date->dayOfWeekIso;
            $previousMonth = $date->copy()->subMonth();
            $daysInPreviousMonth = $previousMonth->daysInMonth;

            // pull strings safely; use strict empty checks
            $startStr = $this->dateRange['start'] ?? '';
            $endStr   = $this->dateRange['end'] ?? '';

            $selectedStartDate = $startStr !== '' ? Carbon::parse($startStr) : null;
            $selectedEndDate   = $endStr !== ''   ? Carbon::parse($endStr)   : null;

            /** @var list<array{day:int,month:int,year:int,isSelectedStart:bool,isSelectedEnd:bool,isBetween:bool,isToday:bool}> $daysFlat */
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
                        'isSelectedStart' => $selectedStartDate && $selectedStartDate->isSameDay($currentDate),
                        'isSelectedEnd'   => $selectedEndDate && $selectedEndDate->isSameDay($currentDate),
                        'isBetween'       => $selectedStartDate && $selectedEndDate && $currentDate->greaterThan($selectedStartDate) && $currentDate->lessThan($selectedEndDate),
                        'isToday' => $currentDate->isToday(),
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
                        'isSelectedStart' => $selectedStartDate && $selectedStartDate->isSameDay($currentDate),
                        'isSelectedEnd'   => $selectedEndDate && $selectedEndDate->isSameDay($currentDate),
                        'isBetween'       => $selectedStartDate && $selectedEndDate && $currentDate->greaterThan($selectedStartDate) && $currentDate->lessThan($selectedEndDate),
                        'isToday' => $currentDate->isToday(),
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
                        'isSelectedStart' => $selectedStartDate && $selectedStartDate->isSameDay($currentDate),
                        'isSelectedEnd'   => $selectedEndDate && $selectedEndDate->isSameDay($currentDate),
                        'isBetween'       => $selectedStartDate && $selectedEndDate && $currentDate->greaterThan($selectedStartDate) && $currentDate->lessThan($selectedEndDate),
                        'isToday' => $currentDate->isToday(),
                    ];
                }
            }

            /** @var list<list<array{day:int,month:int,year:int,isSelectedStart:bool,isSelectedEnd:bool,isBetween:bool,isToday:bool}>> $weeks */
            $weeks = array_chunk($daysFlat, 7);
            $this->days = $weeks;
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

    public function selectDate(int $day, int $month, int $year): void
    {
        $date = Carbon::create($year, $month, $day);
        if ($date) {
            $formattedDate = $date->format('Y-m-d');

            $start = $this->dateRange['start'] ?? '';
            $end   = $this->dateRange['end'] ?? '';

            if ($start === '') {
                $this->dateRange['start'] = $formattedDate;
            } elseif ($start && $end) {
                // both already set → restart with new start, clear end
                $this->dateRange['start'] = $formattedDate;
                $this->dateRange['end'] = '';
            } else {
                if ($formattedDate === $start) {
                    return;
                } elseif ($formattedDate < $start) {
                    $this->dateRange['start'] = $formattedDate;
                    $this->dateRange['end'] = '';
                } else {
                    $this->dateRange['end'] = $formattedDate;
                }
            }
        }

        $this->updateCalendar();
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

    #[On('clean-date-range')]
    public function cleanUp(): void
    {
        $this->dateRange = [
            'start' => '',
            'end' => '',
        ];

        $this->updateCalendar();
    }


    public function render()
    {
        return view('livewire.date-range-picker');
    }
}
