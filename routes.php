<?php

use Mercator\Calendar\Controllers\Rss;
use Mercator\Calendar\Controllers\Ical; // <-- This line is required

Route::get('/mercator/calendar/rss/{slug}', function ($slug) {
    return (new Rss())->feed($slug);
});

Route::get('/mercator/calendar/ical/{slug}.ics', function ($slug) {
    return (new Ical())->feed($slug);
});
Route::get('/mercator/calendar/ical/event/{id}.ics', function ($id) {
    return (new Ical())->eventFeed($id);
});
