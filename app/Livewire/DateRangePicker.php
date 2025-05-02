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

    /** @var array<string, string> */
    #[Modelable]
    public array $dateRange = [];

    /** @var array<int, array<string, int|bool>> */
    public $days = []; // Full calendar with padding

    public function mount(): void
    {
        $this->updateStoredDates(Carbon::now());
        $this->updateCalendar();
    }

    public function updateCalendar(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1);
        if ($date) {
            $this->daysInMonth = $date->daysInMonth;
            $this->monthName = ucfirst($date->translatedFormat('F'));

            // Determine the first day of the current month (1=Monday, 7=Sunday)
            $firstDay = $date->dayOfWeekIso;

            $previousMonth = $date->copy()->subMonth();
            $daysInPreviousMonth = $previousMonth->daysInMonth;

            $this->days = [];

            $selectedStartDate = $this->dateRange['start'] ? Carbon::parse($this->dateRange['start']) : null;
            $selectedEndDate = $this->dateRange['end'] ? Carbon::parse($this->dateRange['end']) : null;

            // Add days from the previous month
            for ($i = $firstDay - 1; $i > 0; $i--) {
                $day = $daysInPreviousMonth - $i + 1;
                $currentDate = Carbon::create($this->previousYear, $this->previousMonth, $day);
                if ($currentDate) {
                    $isSelectedStart = $selectedStartDate && $selectedStartDate->isSameDay($currentDate);
                    $isSelectedEnd = $selectedEndDate && $selectedEndDate->isSameDay($currentDate);
                    $isBetween = $selectedStartDate && $selectedEndDate && $currentDate->greaterThan($selectedStartDate) && $currentDate->lessThan($selectedEndDate);

                    $this->days[] = [
                        'day' => $day,
                        'month' => $this->previousMonth,
                        'year' => $this->previousYear,
                        'isSelectedStart' => $isSelectedStart,
                        'isSelectedEnd' => $isSelectedEnd,
                        'isBetween' => $isBetween,
                        'isToday' => $currentDate->isToday(),
                    ];
                }
            }

            // Add days from the current month
            for ($i = 1; $i <= $this->daysInMonth; $i++) {
                $currentDate = Carbon::create($this->currentYear, $this->currentMonth, $i);

                if ($currentDate) {
                    $isSelectedStart = $selectedStartDate && $selectedStartDate->isSameDay($currentDate);
                    $isSelectedEnd = $selectedEndDate && $selectedEndDate->isSameDay($currentDate);
                    $isBetween = $selectedStartDate && $selectedEndDate && $currentDate->greaterThan($selectedStartDate) && $currentDate->lessThan($selectedEndDate);

                    $this->days[] = [
                        'day' => $i,
                        'month' => $this->currentMonth,
                        'year' => $this->currentYear,
                        'isSelectedStart' => $isSelectedStart,
                        'isSelectedEnd' => $isSelectedEnd,
                        'isBetween' => $isBetween,
                        'isToday' => $currentDate->isToday(),
                    ];
                }
            }

            // Add days from the next month
            $remainingCells = 7 - (count($this->days) % 7);
            $remainingCells = $remainingCells === 7 ? 0 : $remainingCells;

            for ($i = 1; $i <= $remainingCells; $i++) {
                $currentDate = Carbon::create($this->nextYear, $this->nextMonth, $i);

                if ($currentDate) {
                    $isSelectedStart = $selectedStartDate && $selectedStartDate->isSameDay($currentDate);
                    $isSelectedEnd = $selectedEndDate && $selectedEndDate->isSameDay($currentDate);
                    $isBetween = $selectedStartDate && $selectedEndDate && $currentDate->greaterThan($selectedStartDate) && $currentDate->lessThan($selectedEndDate);

                    $this->days[] = [
                        'day' => $i,
                        'month' => $this->nextMonth,
                        'year' => $this->nextYear,
                        'isSelectedStart' => $isSelectedStart,
                        'isSelectedEnd' => $isSelectedEnd,
                        'isBetween' => $isBetween,
                        'isToday' => $currentDate->isToday(),
                    ];
                }
            }
        }

        $this->days = array_chunk($this->days, 7);
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
            $formatedDate = $date->format('Y-m-d');

            if ($this->dateRange['start'] == '') {
                $this->dateRange['start'] = $date->format('Y-m-d');
            } elseif ($this->dateRange['start'] != '' && $this->dateRange['end'] != '') {
                $this->dateRange['start'] = $date->format('Y-m-d');
                $this->dateRange['end'] = '';
            } else {
                if ($formatedDate === $this->dateRange['start']) {
                    return;
                } elseif ($formatedDate < $this->dateRange['start']) {
                    $this->dateRange['start'] = $formatedDate;
                    $this->dateRange['end'] = '';
                } else {
                    $this->dateRange['end'] = $formatedDate;
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
