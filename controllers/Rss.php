<?php namespace Mercator\Calendar\Controllers;

use Cms\Classes\Controller as CmsController;
use Mercator\Calendar\Models\Calendar;
use Response;
use Request;

class Rss extends CmsController
{
	public function feed($slug)
    {
        $calendar = Calendar::where('slug', $slug)->with(['entries' => function ($query) {
            $query->isPublished()->orderBy('start_datetime', 'desc')->limit(20);
        }])->first();

        if (!$calendar) {
            return Response::make('Calendar not found', 404);
        }

        $this->vars['calendar'] = $calendar;
        $this->vars['entries'] = $calendar->entries;
        $this->vars['link'] = Request::url();

        return Response::make($this->renderPartial('@rss.htm'))
            ->header('Content-Type', 'application/xml');
    }}
