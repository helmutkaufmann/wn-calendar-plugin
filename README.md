# Calendar Plugin for Winter CMS

A flexible and robust plugin for managing and displaying calendars and events on your Winter CMS website. This plugin provides a full backend interface for managing multiple calendars and their entries, and includes a powerful, configurable frontend block for easy display.

-----

## Features

  * **Multiple Calendars**: Create and manage multiple, distinct calendars (e.g., "Company Events", "Public Holidays").
  * **Detailed Events**: Create calendar entries with a title, optional summary, HTML description, an optional featured image, a specific start and optional end time, location, and timezone.
  * **Publishing Control**: Each event has optional "publish from" and "publish to" dates, giving you full control over its visibility.
  * **Powerful Frontend Block**: A single, versatile block to display your calendar on any Winter CMS Static Page.
  * **Dual Frontend Views**:
      * **List View**: A collapsible accordion-style list where each event is its own item.
      * **Grid View**: A traditional monthly calendar grid.
  * **Interactive UI**:
      * Click on events in the grid view to see full details in a popover card.
      * The list view accordion can be configured to start expanded and allow multiple events to be open at once.
  * **Event Feeds**: Each calendar automatically generates standard **RSS** and **iCal (.ics)** feeds for syndication and for subscribing with calendar applications like Google Calendar, Apple Calendar, and Outlook.
  * **Multi-lingual Support**: Weekday names in the grid view are automatically translated if the [Winter.Translate plugin](https://www.google.com/search?q=https://wintercms.com/plugin/winter-translate) is installed.
  * **Backend Tools**: Includes a "Purge" function to easily delete old events from a calendar before a specified date.

-----

## Installation

1.  Place the plugin's files in the `plugins/mercator/calendar` directory of your Winter CMS project.
2.  In your project's root directory, run the following command from your terminal to run the database migrations:
    ```bash
    php artisan winter:up
    ```

-----

## Usage

### 1\. Backend Management

After installation, a new **Calendar** item will appear in the top navigation bar of the backend.

  * **Calendars**: First, create one or more calendars. Each calendar is a container for events (e.g., "Public Events").
  * **Entries**: Create your individual events here. When creating an entry, you will fill in its details (title, start/end time, timezone, etc.) and assign it to a specific calendar. The side-menu in the entries view will dynamically list your created calendars for easy filtering.

### 2\. Frontend Display (The Calendar Block)

The primary way to display a calendar is by using the **"Calendar"** block on a Static Page.

1.  In the backend, navigate to **Pages**.
2.  Open the page where you want the calendar to appear.
3.  Click the **Page Builder** tab and add the **"Calendar"** block.
4.  Configure the block using the settings described below. The most important setting is to select the **Calendar** you wish to display.
5.  Save the page.

-----

## Block Configuration

The Calendar block offers the following settings:

| Setting                      | Description                                                                                              | View(s)      |
| ---------------------------- | -------------------------------------------------------------------------------------------------------- | ------------ |
| **Calendar** | Selects which calendar's events to display. This is a required field.                                    | Both         |
| **Display View** | Choose between the `List View` (accordion) and `Grid View` (monthly calendar).                           | Both         |
| **Start with all days expanded** | If checked, all accordion items in the list view will be open by default.                                | List View    |
| **Limit displayed events** | If specified, only this many events will be shown from within the calculated date range.                 | List View    |
| **Start Date** | The date to start displaying events from. If left blank, it defaults to the current day or month.        | Both         |
| **Months to Show** | The number of months of events to display from the start date.                                           | Both         |

-----

## Event Feeds

Each calendar has its own RSS and iCal feed, which are automatically generated. The URL structures are:

  * **RSS**: `https://your-domain.com/mercator/calendar/rss/{slug}`
  * **iCal**: `https://your-domain.com/mercator/calendar/ical/{slug}.ics`

Replace `{slug}` with the actual slug of your calendar. These URLs are also linked directly from the header of the Calendar Block on the frontend.
