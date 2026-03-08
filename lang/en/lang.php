<?php return [
    'plugin' => [
        'name' => 'Calendar',
        'description' => 'A plugin to manage calendars and events.',
    ],
    'navigation' => [
        'main' => 'Calendars',
        'calendars' => 'Calendars',
        'entries' => 'Entries',
    ],
    'permissions' => [
        'tab' => 'Calendar',
        'access_calendars' => 'Manage Calendars',
        'access_entries' => 'Manage Calendar Entries',
    ],
    'calendar' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'label' => 'Calendar',
        'new' => 'New Calendar',
        'return_to_list' => 'Return to calendars list',
    ],
    'entry' => [
        'title' => 'Title',
        'event_date' => 'Date',
        'event_time' => 'Time',
        'description' => 'Description',
        'location' => 'Location',
        'calendar' => 'Calendar',
        'published_from' => 'Published From',
        'published_to' => 'Published To',
        'label' => 'Entry',
        'new' => 'New Entry',
        'return_to_list' => 'Return to entries list',
        'tab_publish' => 'Publishing',
    ],
    'component' => [
        'entry' => [
            'name'        => 'Calendar Entry',
            'description' => 'Displays a single calendar event.',
            'entry'       => 'Entry',
            'entry_description' => 'Select the calendar entry to display (or use a URL parameter).',
            'show_ics'    => "Show 'Add to Calendar' button"
        ],
    ],
    'block' => [
        'entry' => [
            'name'        => 'Calendar Entry',
            'description' => 'Displays a single calendar event as a reusable block.',
            'fields' => [
                'entry_id' => 'Entry',
                'show_ics' => "Show 'Add to Calendar' button"
            ],
        ],
    ],
];