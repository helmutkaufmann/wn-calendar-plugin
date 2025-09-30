<?php namespace Mercator\Calendar\Controllers;

use Cms\Classes\Controller as CmsController;
use Mercator\Calendar\Models\Calendar as CalendarModel;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Carbon\Carbon;
use Response;

class Ical extends CmsController
{
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

            // Create timezone-aware Carbon instances by parsing the raw datetime
            // string from the DB with the event's specific timezone.
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

        return Response::make($icalCalendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8');
    }
}
