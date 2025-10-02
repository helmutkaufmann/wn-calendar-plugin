<?php namespace Mercator\Calendar\Controllers;

use Cms\Classes\Controller as CmsController;
use Mercator\Calendar\Models\Calendar as CalendarModel;
use Mercator\Calendar\Models\CalendarEntry;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Carbon\Carbon;
use Response;

class Ical extends CmsController
{
    /**
     * Generates an iCal feed for an entire calendar subscription.
     */
    public function feed($slug)
    {
        $calendarModel = CalendarModel::where('slug', $slug)->first();

        if (!$calendarModel) {
            return Response::make('Calendar not found', 404);
        }
        
        $start = Carbon::now()->subYear();
        $end = Carbon::now()->addYear();

        $entries = $calendarModel->entries()
            ->isPublished()
            ->betweenDates($start, $end)
            ->get();

        $icalCalendar = Calendar::create($calendarModel->name);

        foreach ($entries as $entry) {
            $description = '';
            if ($entry->summary) {
                $description .= $entry->summary . "\n\n";
            }
            if ($entry->description) {
                $description .= strip_tags($entry->description);
            }

            $startTime = Carbon::parse($entry->start_datetime, $entry->timezone);
            $endTime = $entry->end_datetime
                ? Carbon::parse($entry->end_datetime, $entry->timezone)
                : $startTime->copy()->addHour();

            $event = Event::create()
                ->name($entry->title)
                ->description($description)
                ->startsAt($startTime)
                ->endsAt($endTime);

            if ($entry->location) {
                $event->address($entry->location);
            }

            if ($entry->featured_image) {
                $event->image($entry->featured_image->getPath());
            }
            
            $icalCalendar->event($event);
        }

        // Return the feed without the 'Content-Disposition' header to allow for subscribing
        return Response::make($icalCalendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8');
    }

    /**
     * Generates an iCal file for a single event download.
     */
    public function eventFeed($id)
    {
        $entry = CalendarEntry::find($id);

        if (!$entry) {
            return Response::make('Event not found', 404);
        }

        $icalCalendar = Calendar::create($entry->title);

        $startTime = Carbon::parse($entry->start_datetime, $entry->timezone);
        $endTime = $entry->end_datetime
            ? Carbon::parse($entry->end_datetime, $entry->timezone)
            : $startTime->copy()->addHour();

        $event = Event::create()
            ->name($entry->title)
            ->description($entry->summary ? $entry->summary . "\n\n" . strip_tags($entry->description) : strip_tags($entry->description))
            ->startsAt($startTime)
            ->endsAt($endTime);

        if ($entry->location) {
            $event->address($entry->location);
        }

        if ($entry->featured_image) {
            $event->image($entry->featured_image->getPath());
        }

        $icalCalendar->event($event);
        
        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '', $entry->title) . '.ics';

        // Set Content-Disposition to 'inline' to suggest opening the file directly
        return Response::make($icalCalendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }
}
