<?php

namespace Mercator\Calendar\Components;

use Mercator\Calendar\Models\Calendar;
use Mercator\Calendar\Classes\CalendarService;
use Cms\Classes\ComponentBase;

class Calendars extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Calendar Component',
            'description' => 'The calendar component'
        ];
    }

    function init()
    {
        $properties = [
            'calendar' => $this->property('calendar'),
            'view' => $this->property('view'),
            'listAccordionsOpen' => $this->property('listAccordionsOpen'),
            'displayLimit' => $this->property('displayLimit'),
            'showEventIcsButton' => $this->property('showEventIcsButton'),
            'buttonAlignment' => $this->property('buttonAlignment'),
            'startDate' => $this->property('startDate'),
            'monthsToShow' => $this->property('monthsToShow'),
        ];

        $config = CalendarService::getCalendarConfig($properties['calendar']);
        if (!$config) return;

        $displayProps = CalendarService::getDisplayProperties($properties);
        $data = CalendarService::getCalendarData($config['calendar'], array_merge($properties, $displayProps));

        foreach (array_merge($config, $displayProps, $data) as $key => $value) {
            $this->page[$key] = $value;
        }
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties(): array
    {
        return [
            'calendar' => [
                'title' => 'Calendar',
                'description' => 'Select the calendar to display events from.',
                'type' => 'dropdown',
                'default' => '',
                'placeholder' => 'Select a calendar',
            ],
            'view' => [
                'title' => 'Display View',
                'description' => 'Show events as a list or in a calendar.',
                'type' => 'dropdown',
                'default' => 'list',
                'options' => [
                    'list' => 'List View',
                    'grid' => 'Grid View',
                ],
            ],
            'showEventIcsButton' => [
                'title' => "Show 'Add to Calendar' icon for each event",
                'description' => "If checked each event will have its own download icon.",
                'type' => 'checkbox',
                'default' => true,
            ],
            'listAccordionsOpen' => [
                'title' => 'Show all event details',
                'description' => 'If checked, details for all events will be shown.',
                'type' => 'checkbox',
                'default' => false,
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
                    'right' => 'Right',
                ],
            ],
            'displayLimit' => [
                'title' => 'Limit displayed events',
                'description' => 'If specified, only this many events will be shown from the date range.',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The display limit must be a number.',
            ],
            'startDate' => [
                'title' => 'Start Date',
                'description' => 'The date to start displaying events from. Leave blank for the current day/month.',
                'type' => 'string',
            ],
            'monthsToShow' => [
                'title' => 'Months to Show',
                'description' => 'The number of months of events to display from the start date.',
                'type' => 'string',
                'default' => '3',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Months to show must be a number.',
            ],
        ];
    }

    public function getCalendarOptions()
    {
        return Calendar::orderBy('name')->lists('name', 'id');
    }
}
