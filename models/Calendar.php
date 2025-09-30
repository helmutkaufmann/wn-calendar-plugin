<?php namespace Mercator\Calendar\Models;

use Model;

class Calendar extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sluggable;

    public $table = 'mercator_calendar_calendars';
    protected $slugs = ['slug' => 'name'];

    public $rules = [
        'name' => 'required|string|max:191',
        'slug' => 'required|string|max:191|unique:mercator_calendar_calendars',
    ];

    public $hasMany = [
        'entries' => ['Mercator\Calendar\Models\CalendarEntry', 'scope' => 'sorted'],
    ];
}
