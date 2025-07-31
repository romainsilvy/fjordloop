<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Travel;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class WeekCalendar extends Component
{
    public int $currentYear;
    public int $currentMonth;
    public int $currentWeek;

    public string $startDateString;
    public string $endDateString;
    public Carbon $startDate;
    public Carbon $endDate;

    public Travel $travel;

    /** @var array<int, array<string, mixed>> */
    public $days = []; // Days of the current week

    public function mount(Travel $travel): void
    {
        $this->travel = $travel;

        $today        = Carbon::today();
        $travelStart  = $travel->start_date ?? $today;
        $travelEnd    = $travel->end_date   ?? $travelStart;

        if ($today->between($travelStart, $travelEnd)) {
            $this->startDate = $today->copy()->startOfWeek(Carbon::MONDAY);
        } else {
            $this->startDate = $travelStart->copy()->startOfWeek(Carbon::MONDAY);
        }

        $this->updateStoredDates($this->startDate);
        $this->updateCalendar();
    }

    #[On('activityCreated')]
    #[On('housingCreated')]
    public function updateCalendar(): void
    {
        $this->days = [];

        // Generate 7 days for the week
        for ($i = 0; $i < 7; $i++) {
            $currentDate = $this->startDate->copy()->addDays($i);

            $this->days[] = [
                'day' => $currentDate->day,
                'month' => $currentDate->month,
                'year' => $currentDate->year,
                'dayName' => ucfirst($currentDate->translatedFormat('l')), // Full day name
                'shortDayName' => ucfirst($currentDate->translatedFormat('D')), // Short day name
                'date' => $currentDate->format('Y-m-d'),
                'isToday' => $currentDate->isToday(),
                'events' => $this->travel->getDayEvents($currentDate),
            ];
        }

        // Update the current date range strings for display
        $this->startDateString = $this->startDate->translatedFormat('d M Y');
        $this->endDateString = $this->endDate->translatedFormat('d M Y');
    }



    public function next(): void
    {
        $this->startDate = $this->startDate->copy()->addWeek();
        $this->updateStoredDates($this->startDate);
        $this->updateCalendar();
    }

    public function previous(): void
    {
        $this->startDate = $this->startDate->copy()->subWeek();
        $this->updateStoredDates($this->startDate);
        $this->updateCalendar();
    }

    public function updateStoredDates(Carbon $date): void
    {
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->currentWeek = $date->weekOfYear;

        $this->startDate = $date->copy()->startOfWeek(Carbon::MONDAY);
        $this->endDate = $date->copy()->endOfWeek(Carbon::SUNDAY);
    }

    public function render()
    {
        return view('livewire.week-calendar');
    }
}
