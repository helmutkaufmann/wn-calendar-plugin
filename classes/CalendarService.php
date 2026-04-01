<?php
namespace Mercator\Calendar\Classes;

use Mercator\Calendar\Models\Calendar;
use Mercator\Calendar\Models\CalendarEntry;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Winter\Translate\Classes\Translator;
use Url;
class CalendarService
{
    public static function getCalendarConfig($calendarId)
    {
        if (!$calendarId) return null;

        $calendar = Calendar::find($calendarId);
        if (!$calendar) return null;

        $icsUrl = Url::to('/mercator/calendar/ical/' . $calendar->slug . '.ics');

        return [
            'calendar' => $calendar,
            'icsUrl' => $icsUrl,
            'webcalUrl' => str_replace(['http://', 'https://'], 'webcal://', $icsUrl),
        ];
    }

    public static function getDisplayProperties(array $properties): array
    {
        return [
            'view' => $properties['view'] ?? 'list',
            'listAccordionsOpen' => (bool) ($properties['listAccordionsOpen'] ?? false),
            'displayLimit' => (int) ($properties['displayLimit'] ?? 0),
            'showEventIcsButton' => (bool) ($properties['showEventIcsButton'] ?? true),
            'buttonAlignment' => $properties['buttonAlignment'] ?? 'right',
            'startDate' => $properties['startDate'] ?? null,
            'monthsToShow' => (int) ($properties['monthsToShow'] ?: 3),
        ];
    }

    public static function getCalendarData($calendar, array $properties): array
    {
        $view = $properties['view'];
        $locale = self::getActiveLocale();

        if ($view === 'grid') {
            return self::getGridData($calendar, $properties, $locale);
        }

        return self::getListData($calendar, $properties);
    }

    protected static function getActiveLocale(): string
    {
        if (class_exists(Translator::class)) {
            return Translator::instance()->getLocale();
        }
        return 'en';
    }

    protected static function getGridData($calendar, array $properties, string $activeLocale): array
    {
        $weekdays = [];
        // Start week on Monday, as is common in many locales
        $day = Carbon::now()->startOfWeek(Carbon::MONDAY);
        for ($i = 0; $i < 7; $i++) {
            $weekdays[] = $day->copy()->locale($activeLocale)->isoFormat('ddd');
            $day->addDay();
        }

        // Prepare Calendar Data
        $startDateConfig = $properties['startDate'];
        $monthsToShow = (int) ($properties['monthsToShow'] ?: 1);

        // Start month based on config or current month
        $startOfMonth = $startDateConfig ? Carbon::parse($startDateConfig)->startOfMonth() : Carbon::now()->startOfMonth();
        $calendarData = [];

        for ($m = 0; $m < $monthsToShow; $m++) {
            $currentMonth = $startOfMonth->copy()->addMonthsNoOverflow($m);
            // Calculate display range to show a full week (Mon-Sun) that contains the month
            $monthStart = $currentMonth->copy()->startOfWeek(Carbon::MONDAY);
            $monthEnd = $currentMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

            $entriesByDate = CalendarEntry::where('calendar_id', $calendar->id)
                ->isPublished()
                ->betweenDates($monthStart, $monthEnd)
                ->orderBy('start_datetime', 'asc')
                ->get()
                ->groupBy(function($entry) {
                    return Carbon::parse($entry->start_datetime)->format('Y-m-d');
                });

            $period = CarbonPeriod::create($monthStart, $monthEnd);
            $weeks = [];
            $week = [];

            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');
                $dayData = [
                    'date' => $date,
                    'isToday' => $date->isToday(),
                    'inMonth' => $date->month == $currentMonth->month,
                    'entries' => $entriesByDate->get($dateKey) ?: []
                ];
                $week[] = $dayData;

                // End of week (Sunday)
                if ($date->isSunday()) {
                    $weeks[] = $week;
                    $week = [];
                }
            }
            // Add any remaining days if the period didn't end on Sunday
            if (!empty($week)) {
                 $weeks[] = $week;
            }

            $calendarData[] = [
                'monthName' => $currentMonth->locale($activeLocale)->isoFormat('MMMM YYYY'),
                'weeks' => $weeks
            ];
        }

        return [
            'weekdays' => $weekdays,
            'calendarData' => $calendarData,
        ];
    }

    protected static function getListData($calendar, array $properties): array
    {
        $startDateConfig = $properties['startDate'];
        $monthsToShow = $properties['monthsToShow'];
        $displayLimit = $properties['displayLimit'];

        $startDate = $startDateConfig
            ? Carbon::parse($startDateConfig)->startOfDay()
            : Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addMonths($monthsToShow)->endOfMonth()->endOfDay();

        $entries = CalendarEntry::where('calendar_id', $calendar->id)
            ->isPublished()
            ->betweenDates($startDate, $endDate)
            ->orderBy('start_datetime', 'asc')
            ->get();

        if ($displayLimit > 0) {
            $entries = $entries->take($displayLimit);
        }

        return [
            'groupedEntries' => $entries->groupBy(fn($entry) =>
                Carbon::parse($entry->start_datetime)->format('Y-m-d')
            ),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}
