<?php namespace Mercator\Calendar;

use System\Classes\PluginBase;
use Backend;
use Event;
class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'mercator.calendar::lang.plugin.name',
            'description' => 'mercator.calendar::lang.plugin.description',
            'author'      => 'Helmut Kaufmann',
            'icon'        => 'icon-calendar-o'
        ];
    }

    public function registerNavigation()
    {
        return [
            'calendar' => [
                'label'       => 'mercator.calendar::lang.navigation.main',
                'url'         => Backend::url('mercator/calendar/calendars'),
                'icon'        => 'icon-calendar-o',
                'permissions' => ['mercator.calendar.access_calendars'],
                'order'       => 500,

                'sideMenu' => [
                    'calendars' => [
                        'label'       => 'mercator.calendar::lang.navigation.calendars',
                        'icon'        => 'icon-calendar',
                        'url'         => Backend::url('mercator/calendar/calendars'),
                        'permissions' => ['mercator.calendar.access_calendars'],
                    ],
                    'entries' => [
                        'label'       => 'mercator.calendar::lang.navigation.entries',
                        'icon'        => 'icon-calendar-check-o',
                        'url'         => Backend::url('mercator/calendar/calendarentries'),
                        'permissions' => ['mercator.calendar.access_entries'],
                    ],
                ]
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'mercator.calendar.access_calendars' => [
                'tab'   => 'mercator.calendar::lang.permissions.tab',
                'label' => 'mercator.calendar::lang.permissions.access_calendars'
            ],
            'mercator.calendar.access_entries' => [
                'tab'   => 'mercator.calendar::lang.permissions.tab',
                'label' => 'mercator.calendar::lang.permissions.access_entries'
            ],
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
	/**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        Event::listen('backend.menu.extendItems', function ($manager) {
            // Get the context object
            $context = $manager->getContext();

            // Only apply to the main Calendar navigation item
            if ($context->owner !== 'Mercator.Calendar' || $context->mainMenuCode !== 'calendar') {
                return;
            }

            // --- Corrected Logic ---

            // 1. Prepare the dynamic items from the database
            $calendars = Calendar::orderBy('name')->get();
            $dynamicItems = [];
            foreach ($calendars as $calendar) {
                $dynamicItems['calendar-' . $calendar->slug] = [
                    'label'       => $calendar->name,
                    'icon'        => 'icon-calendar-check-o',
                    'url'         => Backend::url('mercator/calendar/calendarentries?Filter[calendar]=' . $calendar->id),
                    'permissions' => ['mercator.calendar.access_entries'],
                ];
            }

            // 2. Define the static item that will be modified
            $staticItems = [
                'entries' => [
                    'label'       => 'All Entries',
                    'icon'        => 'icon-list',
                    'url'         => Backend::url('mercator/calendar/calendarentries'),
                    'permissions' => ['mercator.calendar.access_entries'],
                ],
            ];
            
            // 3. Merge the static and dynamic items into a single array
            $allItems = array_merge($staticItems, $dynamicItems);

            // 4. Add the merged array to the side menu
            $manager->addSideMenuItems('Mercator.Calendar', 'calendar', $allItems);
        });
    }
    
    public function registerBlocks(): array
    {
        return [
            'mercal_calendar' => '$/mercator/calendar/blocks/calendar.block',
        ];
    }
}
