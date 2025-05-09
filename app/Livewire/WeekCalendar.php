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

    public $events = [];
    public Travel $travel;

    /** @var array<int, array<string, mixed>> */
    public $days = []; // Days of the current week

    public function mount(Travel $travel): void
    {
        $this->travel = $travel;

        // Start with the current week
        $this->startDate = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $this->updateStoredDates($this->startDate);
        $this->updateCalendar();
    }

    public function getTravelEvents()
    {
        $activities = $this->travel->activities()->get();
        $housings = $this->travel->housings()->get();
        $this->events = [];

        foreach ($activities as $activity) {
            $this->events[] = [
                'name' => $activity->name,
                'start_date' => $activity->start_date,
                'end_date' => $activity->end_date,
                'start_time' => $activity->start_time,
                'end_time' => $activity->end_time,
            ];
        }

        foreach ($housings as $housing) {
            $this->events[] = [
                'name' => $housing->name,
                'start_date' => $housing->start_date,
                'end_date' => $housing->end_date,
                'start_time' => $housing->start_time,
                'end_time' => $housing->end_time,
            ];
        }
    }

    #[On('activityCreated')]
    #[On('housingCreated')]
    public function updateCalendar(): void
    {
        $this->getTravelEvents();

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
                'events' => $this->getDayEvents($currentDate),
            ];
        }

        // Update the current date range strings for display
        $this->startDateString = $this->startDate->translatedFormat('d M Y');
        $this->endDateString = $this->endDate->translatedFormat('d M Y');
    }

    public function getDayEvents($day)
    {
        $events = [];
        foreach ($this->events as $event) {
            $startDate = Carbon::parse($event['start_date']);
            $endDate = Carbon::parse($event['end_date']);

            if ($day->between($startDate, $endDate)) {
                $events[] = [
                    'name' => $event['name'],
                    'start_time' => $event['start_time'],
                    'end_time' => $event['end_time'],
                ];
            }
        }

        usort($events, function ($a, $b) {
            $aTime = $a['start_time'];
            $bTime = $b['start_time'];
            return strcmp($aTime, $bTime);
        });

        return $events;
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
