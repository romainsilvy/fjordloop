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


    /** @var array<int, array<string, int|bool>> */
    public $days = []; // Full calendar with padding

    public function mount(Travel $travel): void
    {
        $this->updateStoredDates(Carbon::now());
        $this->updateCalendar();

        $this->travel = $travel;
    }

    #[On('activityCreated')]
    #[On('housingCreated')]
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

            // Add days from the previous month
            for ($i = $firstDay - 1; $i > 0; $i--) {
                $day = $daysInPreviousMonth - $i + 1;
                $currentDate = Carbon::create($this->previousYear, $this->previousMonth, $day);
                if ($currentDate) {

                    $this->days[] = [
                        'day' => $day,
                        'month' => $this->previousMonth,
                        'year' => $this->previousYear,
                        'isToday' => $currentDate->isToday(),
                        'events' => $this->travel->getDayEvents($currentDate),
                    ];
                }
            }

            // Add days from the current month
            for ($i = 1; $i <= $this->daysInMonth; $i++) {
                $currentDate = Carbon::create($this->currentYear, $this->currentMonth, $i);

                if ($currentDate) {
                    $this->days[] = [
                        'day' => $i,
                        'month' => $this->currentMonth,
                        'year' => $this->currentYear,
                        'isToday' => $currentDate->isToday(),
                        'events' => $this->travel->getDayEvents($currentDate),
                    ];
                }
            }

            // Add days from the next month
            $remainingCells = 7 - (count($this->days) % 7);
            $remainingCells = $remainingCells === 7 ? 0 : $remainingCells;

            for ($i = 1; $i <= $remainingCells; $i++) {
                $currentDate = Carbon::create($this->nextYear, $this->nextMonth, $i);

                if ($currentDate) {
                    $this->days[] = [
                        'day' => $i,
                        'month' => $this->nextMonth,
                        'year' => $this->nextYear,
                        'isToday' => $currentDate->isToday(),
                        'events' => $this->travel->getDayEvents($currentDate),
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
