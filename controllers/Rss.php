<?php namespace Mercator\Calendar\Controllers;

use Mercator\Calendar\Models\Calendar;
use Response;
use Request;
use View;

class Rss
{
    public function feed($slug)
    {
        $calendar = Calendar::where('slug', $slug)->with(['entries' => function ($query) {
            $query->isPublished()->orderBy('start_datetime', 'desc')->limit(20);
        }])->first();

        if (!$calendar) {
            return Response::make('Calendar not found', 404);
        }

        // Prepare variables for the view
        $vars = [
            'calendar' => $calendar,
            'entries'  => $calendar->entries,
            'link'     => Request::url()
        ];

        // Use View::make() with the namespaced view path.
        // This will render the plugins/mercator/calendar/views/rss.htm file.
        $view = View::make('mercator.calendar::rss', $vars);

        return Response::make($view)
            ->header('Content-Type', 'application/xml');
    }
}
