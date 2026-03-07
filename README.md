# Calendar Plugin for Winter CMS Block

A flexible and robust plugin for managing and displaying calendars and events on your Winter CMS website. This plugin provides a full backend interface for managing multiple calendars and their entries, and includes a powerful, configurable frontend block for easy display.

-----

## Features

  * **Core Functionality**

      * Manage multiple, distinct calendars.
      * Create detailed calendar entries with a title, optional summary, HTML description, an optional featured image, a specific start and optional end time, location, and timezone.
      * Control event visibility with `published_from` and `published_to` dates.

  * **Backend Management**

      * Dynamic side navigation menu that lists all created calendars for easy filtering of the entries list.
      * Default sort order for entries is descending by start date, showing the newest events first.
      * A "Purge" function on the calendar update page to easily delete old events before a specified date.

  * **Powerful Frontend Block**

      * A single, versatile block to display your calendar on any Winter CMS Static Page.
      * **Dual Display Views**:
          * **List View**: A collapsible accordion-style list where each individual event is its own item.
          * **Grid View**: A traditional monthly calendar grid.
      * **Interactive UI**:
          * In Grid View, click on an event to see its full details in a popover card.
          * In List View, the accordion title shows the date, event title, and summary. The expanded content shows full details.
          * The accordion can be configured to start with all items expanded and to allow multiple items to be open simultaneously.
      * **Multi-lingual Support**: All dates, times, and weekday names are automatically translated based on the active locale (requires the Winter.Translate plugin).

  * **Calendar Feeds**

      * **iCal Subscription**: Each calendar provides a `webcal://` subscription link, allowing users to subscribe to the entire calendar in applications like Outlook, Google Calendar, or Apple Calendar.
      * **Single Event Download**: Each event can have an "Add to Calendar" icon to download an `.ics` file for just that single event.
      * **RSS Feed**: Each calendar automatically generates a standard RSS feed for syndication in news readers.

-----

## Dependencies

  * **`spatie/icalendar-generator`**: Required for generating iCal feeds. This is installed via Composer.
  * **`Winter.Translate`** (Optional): Required for the automatic translation of dates and weekday names.

-----

## Installation

1.  Place the plugin's files in the `plugins/mercator/calendar` directory.
2.  Create a `composer.json` file inside `plugins/mercator/calendar/` with the following content:
    ```json
    {
        "name": "mercator/calendar",
        "type": "winter-plugin",
        "require": {
            "spatie/icalendar-generator": "^2.0"
        }
    }
    ```
3.  Open the main `composer.json` file in your Winter CMS project root and add the plugin's `composer.json` path to the `extra.merge-plugin.include` section:
    ```json
    "extra": {
        "merge-plugin": {
            "include": [
                "plugins/mercator/calendar/composer.json"
            ],
            "recurse": true,
            "replace": false,
            "merge-dev": false
        }
    },
    ```
4.  Run `composer update` from your project's root directory to install the required libraries.
5.  Run the database migrations:
    ```bash
    php artisan winter:up
    ```

-----

## Block Configuration

When you add the "Calendar" block to a page, you have the following settings available:

| Setting                      | Description                                                                                              |
| ---------------------------- | -------------------------------------------------------------------------------------------------------- |
| **Calendar** | Selects which calendar's events to display. This is required.                                            |
| **Display View** | Choose between the `List View` (accordion) and `Grid View` (monthly calendar).                           |
| **Feed Button Alignment** | Choose the alignment (`left`, `center`, `right`) for the 'Subscribe' button, or hide it (`Not Shown`). |
| **Show 'Add to Calendar' icon** | If checked, each event will have its own download icon in the detailed view.                               |
| **Start with all days expanded** | (List View only) If checked, all accordion items will be open by default.                                |
| **Limit displayed events** | (List View only) If specified, only this many events will be shown from within the calculated date range. |
| **Start Date** | The date to start displaying events from. If left blank, it defaults to the current day or month.        |
| **Months to Show** | The number of months of events to display from the start date.                                           |

-----

## Feed URLs

The plugin generates feed URLs based on the slug of the calendar you create in the backend.

  * **iCal Subscription**: `https://your-domain.com/mercator/calendar/ical/{slug}.ics`
  * **RSS Feed**: `https://your-domain.com/mercator/calendar/rss/{slug}`


## Individual Event Pages

A new `Calendar Entry` component is available to render the details of a single event. It expects an
`entryId` property (usually supplied via a URL parameter) and exposes an `entry` page variable containing
the model instance.

Example CMS page setup:

```twig
url = "/event/:entry_id"
==
title = "Event details"
layout = "default"

[calendarEntryComponent]
entryId = "{{ :entry_id }}"   <!-- this property is now a dropdown when editing the page, showing all published entries -->
showEventIcsButton = 1
```

### Block for Static Pages

If you prefer to use the feature as a reusable **block** instead of a component, a
`Calendar Entry` block is also provided. It has the same options but renders via the
page designer's block picker and can be placed on any static page or layout.

Fields available:

* **Entry** – recordfinder dropdown listing published entries by title.
* **Show 'Add to Calendar' icon** – toggle the ICS download link.

The block will expose the same `entry` and `icsUrl` variables in the page context. Example
usage in a layout's markup is identical to the component above.

And in the page markup you can reference the entry:

```twig
<h1>{{ entry.title }}</h1>
<p>{{ entry.start_datetime|date("l, F jS, Y g:i A") }}</p>
<!-- etc -->
```

The component will also include an `icsUrl` variable when the `showEventIcsButton` option is enabled,
linking to the existing `/mercator/calendar/ical/event/{id}.ics` route.

