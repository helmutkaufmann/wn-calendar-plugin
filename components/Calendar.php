<?php namespace Mercator\Calendar\Components;

use Cms\Classes\ComponentBase;
use Mercator\Calendar\Models\Calendar as CalendarModel;
use Mercator\Calendar\Models\CalendarEntry;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Winter\Translate\Classes\Translator;
use Url;
use Exception;

class Calendar extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Calendar',
            'description' => 'Displays events from a selected calendar.'
        ];
    }

    public function defineProperties()
    {
        return [
            'calendar_id' => [
                'title' => 'Calendar',
                'description' => 'Select the calendar to display events from.',
                'type' => 'dropdown',
                'default' => '',
                'options' => $this->getCalendarIdOptions()
            ],
            'view' => [
                'title' => 'Display View',
                'description' => 'Show events as a list or in a calendar grid.',
                'type' => 'dropdown',
                'default' => 'list',
                'options' => [
                    'list' => 'List View',
                    'grid' => 'Grid View'
                ]
            ],
            'showEventIcsButton' => [
                'title' => "Show 'Add to Calendar' button for each event",
                'description' => 'If checked each event will have its own download icon.',
                'type' => 'checkbox',
                'default' => 1,
            ],
            'listAccordionsOpen' => [
                'title' => 'Show all event details',
                'description' => 'If checked, detail for all events will be shown.',
                'type' => 'checkbox',
                'default' => 0,
            ],
            'buttonAlignment' => [
                'title' => 'Feed Button Alignment',
                'description' => "Choose the alignment for the 'Subscribe' button, or hide it.",
                'type' => 'dropdown',
                'default' => 'right',
                'options' => [
                    'none' => 'Not Shown',
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ]
            ],
            'displayLimit' => [
                'title' => 'Limit displayed events',
                'description' => 'If specified, only this many events will be shown from the date range.',
                'type' => 'string',
                'default' => '',
            ],
            'startDate' => [
                'title' => 'Start Date',
                'description' => 'The date to start displaying events from. Leave blank for the current day/month.',
                'type' => 'string',
                'default' => '',
            ],
            'monthsToShow' => [
                'title' => 'Months to Show',
                'description' => 'The number of months of events to display from the start date.',
                'type' => 'string',
                'default' => '3',
            ],
        ];
    }

    public function getCalendarIdOptions()
    {
        try {
            $calendars = CalendarModel::orderBy('name')->get();
            $options = ['' => '-- Select a Calendar --'];
            foreach ($calendars as $calendar) {
                $options[$calendar->id] = $calendar->name;
            }
            return $options;
        } catch (\Exception $e) {
            return ['' => '-- Select a Calendar --'];
        }
    }

    public function onRun()
    {
        $this->addCss('/plugins/mercator/calendar/assets/css/calendar-component.css');
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        // --- Get Configuration ---
        $calendarId = $this->property('calendar_id');
        $view = $this->property('view') ?? 'list';

        if (!$calendarId) return;
        
        $calendar = CalendarModel::find($calendarId);
        if (!$calendar) return;

        $this->page['calendar'] = $calendar;
        $this->page['view'] = $view;
        $this->page['listAccordionsOpen'] = (bool) ($this->property('listAccordionsOpen'));
        $this->page['displayLimit'] = (int) ($this->property('displayLimit') ?? 0);
        $this->page['showEventIcsButton'] = (bool) ($this->property('showEventIcsButton'));
        $this->page['buttonAlignment'] = $this->property('buttonAlignment') ?? 'right';
        $this->page['icsUrl'] = Url::to('/mercator/calendar/ical/' . $calendar->slug . '.ics');
        $this->page['webcalUrl'] = str_replace(['http://', 'https://'], 'webcal://', (string) $this->page['icsUrl']);

        // --- Prepare Data based on selected View ---
        if ($view === 'grid') {
            $this->prepareGridView($calendar);
        } else {
            $this->prepareListView($calendar);
        }
    }

    protected function prepareGridView($calendar)
    {
        // Prepare Weekday Headers for Grid View
        if (class_exists(Translator::class)) {
            $translator = Translator::instance();
            $activeLocale = $translator->getLocale();
        } else {
            $activeLocale = 'en';
        }
        
        $weekdays = [];
        // Start week on Monday, as is common in many locales
        $day = Carbon::now()->startOfWeek(Carbon::MONDAY);
        for ($i = 0; $i < 7; $i++) {
            $weekdays[] = $day->copy()->locale($activeLocale)->isoFormat('ddd');
            $day->addDay();
        }
        $this->page['weekdays'] = $weekdays;

        // Prepare Calendar Data
        $startDateConfig = $this->property('startDate');
        $monthsToShow = (int) ($this->property('monthsToShow') ?: 1);
        
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
        $this->page['calendarData'] = $calendarData;
    }

    protected function prepareListView($calendar)
    {
        $displayLimit = (int) $this->property('displayLimit');
        $startDateConfig = $this->property('startDate');
        $monthsToShow = (int) ($this->property('monthsToShow') ?: 3);
        
        $startDate = $startDateConfig ? Carbon::parse($startDateConfig)->startOfDay() : Carbon::now()->startOfDay();
        // End date is based on the number of months to show, adjusted to end of day
        $endDate = $startDate->copy()->addMonths($monthsToShow)->endOfMonth()->endOfDay();

        $entries = CalendarEntry::where('calendar_id', $calendar->id)
            ->isPublished()
            ->betweenDates($startDate, $endDate)
            ->orderBy('start_datetime', 'asc')
            ->get();
            
        if ($displayLimit > 0) {
            $entries = $entries->take($displayLimit);
        }

        // Group events by date for the list view header
        $this->page['groupedEntries'] = $entries->groupBy(function($entry) {
            return Carbon::parse($entry->start_datetime)->format('Y-m-d');
        });
        $this->page['startDate'] = $startDate;
        $this->page['endDate'] = $endDate;
    }
}
