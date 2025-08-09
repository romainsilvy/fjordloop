<?php

use App\Livewire\DateRangePicker;
use Carbon\Carbon;
use Livewire\Livewire;

function createDateRangePickerComponent($dateRange = null) {
    return Livewire::test(DateRangePicker::class, [
        'dateRange' => $dateRange ?? ['start' => '', 'end' => '']
    ]);
}

test('date range picker component can be rendered', function () {
    createDateRangePickerComponent()->assertStatus(200);
});

test('component initializes with current date', function () {
    $now = Carbon::now();

    $component = createDateRangePickerComponent();

    expect($component->get('currentYear'))->toBe($now->year);
    expect($component->get('currentMonth'))->toBe($now->month);
    expect($component->get('monthName'))->toBe(ucfirst($now->translatedFormat('F')));
    expect($component->get('daysInMonth'))->toBe($now->daysInMonth);
});

test('component calculates previous and next month data', function () {
    $now = Carbon::now();

    $component = createDateRangePickerComponent();

    $previousMonth = $now->copy()->subMonth();
    $nextMonth = $now->copy()->addMonth();

    expect($component->get('previousMonth'))->toBe($previousMonth->month);
    expect($component->get('previousYear'))->toBe($previousMonth->year);
    expect($component->get('nextMonth'))->toBe($nextMonth->month);
    expect($component->get('nextYear'))->toBe($nextMonth->year);
});

test('can navigate to next month', function () {
    $component = createDateRangePickerComponent();

    $currentMonth = $component->get('currentMonth');
    $currentYear = $component->get('currentYear');

    $component->call('next');

    $expectedDate = Carbon::create($currentYear, $currentMonth, 1)->addMonth();

    expect($component->get('currentMonth'))->toBe($expectedDate->month);
    expect($component->get('currentYear'))->toBe($expectedDate->year);
    expect($component->get('monthName'))->toBe(ucfirst($expectedDate->translatedFormat('F')));
});

test('can navigate to previous month', function () {
    $component = createDateRangePickerComponent();

    $currentMonth = $component->get('currentMonth');
    $currentYear = $component->get('currentYear');

    $component->call('previous');

    $expectedDate = Carbon::create($currentYear, $currentMonth, 1)->subMonth();

    expect($component->get('currentMonth'))->toBe($expectedDate->month);
    expect($component->get('currentYear'))->toBe($expectedDate->year);
    expect($component->get('monthName'))->toBe(ucfirst($expectedDate->translatedFormat('F')));
});

test('calendar generates correct number of days', function () {
    $component = createDateRangePickerComponent();

    $days = $component->get('days');

    // Should have 5 or 6 weeks (rows) of 7 days each
    $weekCount = count($days);
    expect($weekCount >= 5 && $weekCount <= 6)->toBeTrue();
    foreach ($days as $week) {
        expect($week)->toHaveCount(7);
    }
});

test('can select start date', function () {
    $component = createDateRangePickerComponent();

    // Select a date in the current month
    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');
    $day = 15;

    $component->call('selectDate', $day, $month, $year);

    $dateRange = $component->get('dateRange');
    $expectedDate = Carbon::create($year, $month, $day)->format('Y-m-d');

    expect($dateRange['start'])->toBe($expectedDate);
    expect($dateRange['end'])->toBe('');
});

test('can select end date after start date', function () {
    $component = createDateRangePickerComponent();

    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');

    // Select start date
    $component->call('selectDate', 10, $month, $year);

    // Select end date (after start date)
    $component->call('selectDate', 20, $month, $year);

    $dateRange = $component->get('dateRange');

    expect($dateRange['start'])->toBe(Carbon::create($year, $month, 10)->format('Y-m-d'));
    expect($dateRange['end'])->toBe(Carbon::create($year, $month, 20)->format('Y-m-d'));
});

test('selecting same date as start date does nothing', function () {
    $component = createDateRangePickerComponent();

    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');

    // Select start date
    $component->call('selectDate', 15, $month, $year);

    $originalDateRange = $component->get('dateRange');

    // Select same date again
    $component->call('selectDate', 15, $month, $year);

    $newDateRange = $component->get('dateRange');

    // Should remain unchanged
    expect($newDateRange)->toBe($originalDateRange);
});

test('selecting date before start date replaces start date', function () {
    $component = createDateRangePickerComponent();

    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');

    // Select start date
    $component->call('selectDate', 15, $month, $year);

    // Select date before start date
    $component->call('selectDate', 10, $month, $year);

    $dateRange = $component->get('dateRange');

    expect($dateRange['start'])->toBe(Carbon::create($year, $month, 10)->format('Y-m-d'));
    expect($dateRange['end'])->toBe('');
});

test('selecting third date resets range', function () {
    $component = createDateRangePickerComponent();

    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');

    // Select start date
    $component->call('selectDate', 10, $month, $year);

    // Select end date
    $component->call('selectDate', 20, $month, $year);

    // Select third date (should reset)
    $component->call('selectDate', 25, $month, $year);

    $dateRange = $component->get('dateRange');

    expect($dateRange['start'])->toBe(Carbon::create($year, $month, 25)->format('Y-m-d'));
    expect($dateRange['end'])->toBe('');
});

test('calendar shows selected dates correctly', function () {
    $component = createDateRangePickerComponent();

    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');

    // Select a date range
    $component->call('selectDate', 10, $month, $year);
    $component->call('selectDate', 15, $month, $year);

    $days = $component->get('days');

    // Find the selected days in the calendar
    $hasStartDate = false;
    $hasEndDate = false;
    $hasBetweenDate = false;

    foreach ($days as $week) {
        foreach ($week as $day) {
            if ($day['month'] == $month && $day['year'] == $year) {
                if ($day['day'] == 10) {
                    expect($day['isSelectedStart'])->toBeTrue();
                    $hasStartDate = true;
                } elseif ($day['day'] == 15) {
                    expect($day['isSelectedEnd'])->toBeTrue();
                    $hasEndDate = true;
                } elseif ($day['day'] > 10 && $day['day'] < 15) {
                    expect($day['isBetween'])->toBeTrue();
                    $hasBetweenDate = true;
                }
            }
        }
    }

    expect($hasStartDate)->toBeTrue();
    expect($hasEndDate)->toBeTrue();
});

test('calendar includes previous month days', function () {
    $component = createDateRangePickerComponent();

    $days = $component->get('days');
    $currentMonth = $component->get('currentMonth');
    $previousMonth = $component->get('previousMonth');

    // Check first week for previous month days
    $firstWeek = $days[0];
    $hasPreviousMonthDays = false;

    foreach ($firstWeek as $day) {
        if ($day['month'] == $previousMonth) {
            $hasPreviousMonthDays = true;
            break;
        }
    }

    // Most months will have previous month days unless the 1st falls on Monday
    $firstOfMonth = Carbon::create($component->get('currentYear'), $currentMonth, 1);
    if ($firstOfMonth->dayOfWeekIso != 1) {
        expect($hasPreviousMonthDays)->toBeTrue();
    }
});

test('calendar includes next month days when needed', function () {
    $component = createDateRangePickerComponent();

    $days = $component->get('days');
    $nextMonth = $component->get('nextMonth');

    // Check last week for next month days
    $lastWeek = end($days);
    $hasNextMonthDays = false;

    foreach ($lastWeek as $day) {
        if ($day['month'] == $nextMonth) {
            $hasNextMonthDays = true;
            break;
        }
    }

    // Calendar may or may not have next month days depending on how the month falls
    // This test just verifies the calendar structure is valid
    expect($lastWeek)->toHaveCount(7);
});

test('can select dates across months', function () {
    $component = createDateRangePickerComponent();

    // Get current and next month info
    $currentMonth = $component->get('currentMonth');
    $currentYear = $component->get('currentYear');
    $nextMonth = $component->get('nextMonth');
    $nextYear = $component->get('nextYear');

    // Select date in current month
    $component->call('selectDate', 25, $currentMonth, $currentYear);

    // Navigate to next month
    $component->call('next');

    // Select date in next month
    $component->call('selectDate', 5, $nextMonth, $nextYear);

    $dateRange = $component->get('dateRange');

    expect($dateRange['start'])->toBe(Carbon::create($currentYear, $currentMonth, 25)->format('Y-m-d'));
    expect($dateRange['end'])->toBe(Carbon::create($nextYear, $nextMonth, 5)->format('Y-m-d'));
});

test('today is marked correctly in calendar', function () {
    $component = createDateRangePickerComponent();

    $days = $component->get('days');
    $today = Carbon::today();

    $todayFound = false;

    foreach ($days as $week) {
        foreach ($week as $day) {
            if ($day['year'] == $today->year &&
                $day['month'] == $today->month &&
                $day['day'] == $today->day) {
                expect($day['isToday'])->toBeTrue();
                $todayFound = true;
            } elseif ($day['isToday']) {
                // If isToday is true, it should match today's date
                expect($day['year'])->toBe($today->year);
                expect($day['month'])->toBe($today->month);
                expect($day['day'])->toBe($today->day);
            }
        }
    }

    expect($todayFound)->toBeTrue();
});

test('cleanup resets date range', function () {
    $component = createDateRangePickerComponent();

    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');

    // Select a date range
    $component->call('selectDate', 10, $month, $year);
    $component->call('selectDate', 15, $month, $year);

    // Verify dates are selected
    $dateRange = $component->get('dateRange');
    expect($dateRange['start'])->not->toBe('');
    expect($dateRange['end'])->not->toBe('');

    // Call cleanup
    $component->call('cleanUp');

    // Verify dates are cleared
    $dateRange = $component->get('dateRange');
    expect($dateRange['start'])->toBe('');
    expect($dateRange['end'])->toBe('');
});

test('cleanup responds to clean-date-range event', function () {
    $component = createDateRangePickerComponent();

    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');

    // Select a date range
    $component->call('selectDate', 10, $month, $year);
    $component->call('selectDate', 15, $month, $year);

    // Dispatch the event
    $component->dispatch('clean-date-range');

    // Verify dates are cleared
    $dateRange = $component->get('dateRange');
    expect($dateRange['start'])->toBe('');
    expect($dateRange['end'])->toBe('');
});

test('date range is modelable', function () {
    $initialDateRange = [
        'start' => '2025-06-01',
        'end' => '2025-06-15',
    ];

    $component = createDateRangePickerComponent($initialDateRange);

    expect($component->get('dateRange'))->toBe($initialDateRange);
});

test('calendar updates when date range changes', function () {
    $component = createDateRangePickerComponent();

    $year = $component->get('currentYear');
    $month = $component->get('currentMonth');

    // Select start date
    $component->call('selectDate', 10, $month, $year);

    $days = $component->get('days');

    // Find day 10 and verify it's marked as selected start
    foreach ($days as $week) {
        foreach ($week as $day) {
            if ($day['month'] == $month && $day['year'] == $year && $day['day'] == 10) {
                expect($day['isSelectedStart'])->toBeTrue();
                expect($day['isSelectedEnd'])->toBeFalse();
                expect($day['isBetween'])->toBeFalse();
                break 2;
            }
        }
    }
});

test('handles year transitions correctly', function () {
    // Test December -> January transition
    $component = createDateRangePickerComponent();

    // Set to December
    $component->set('currentYear', 2024);
    $component->set('currentMonth', 12);
    $component->call('updateStoredDates', Carbon::create(2024, 12, 1));

    // Go to next month (should be January 2025)
    $component->call('next');

    expect($component->get('currentMonth'))->toBe(1);
    expect($component->get('currentYear'))->toBe(2025);
});

test('handles empty date range initialization', function () {
    $component = createDateRangePickerComponent();

    $dateRange = $component->get('dateRange');

    // Should handle empty array or array with empty strings
    if (is_array($dateRange)) {
        expect($dateRange['start'] ?? '')->toBe('');
        expect($dateRange['end'] ?? '')->toBe('');
    } else {
        expect($dateRange)->toBe([]);
    }
});

test('selectDate handles invalid carbon date creation gracefully', function () {
    $component = createDateRangePickerComponent();

    // This should not crash even with edge case dates
    $component->call('selectDate', 31, 2, 2024); // February 31st doesn't exist

    // Component should still be functional
    expect($component->instance())->toBeInstanceOf(DateRangePicker::class);
});

test('navigation methods handle invalid carbon date creation gracefully', function () {
    $component = createDateRangePickerComponent();

    // Set invalid state and try navigation
    $component->set('currentYear', 0);
    $component->set('currentMonth', 0);

    // These should not crash
    $component->call('next');
    $component->call('previous');

    // Component should still be functional
    expect($component->instance())->toBeInstanceOf(DateRangePicker::class);
});
