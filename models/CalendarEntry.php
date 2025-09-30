<?php namespace Mercator\Calendar\Models;

use Model;
use Carbon\Carbon;
use DateTimeZone; 

class CalendarEntry extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'mercator_calendar_entries';

    protected $dates = [
        // REMOVED 'start_datetime',
        // REMOVED 'end_datetime',
        'published_from',
        'published_to',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        // REMOVED 'start_datetime' => 'datetime:Y-m-d H:i:s',
        // REMOVED 'end_datetime'   => 'datetime:Y-m-d H:i:s',
    ];

    public $rules = [
        'title'           => 'required|string|max:191',
        'summary'         => 'string|max:255',
        'start_datetime'  => 'required|date',
        'end_datetime'    => 'nullable|date|after:start_datetime',
        'timezone'        => 'nullable|timezone', // Add validation for timezone
        'calendar_id'     => 'required|integer|exists:mercator_calendar_calendars,id',
    ];

    public $belongsTo = [
        'calendar' => 'Mercator\Calendar\Models\Calendar',
    ];

    public $attachOne = [
        'featured_image' => 'System\Models\File'
    ];

    // Update this scope to query the new column
    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query
            ->whereDate('start_datetime', '>=', $startDate->toDateString())
            ->whereDate('start_datetime', '<=', $endDate->toDateString());
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('start_datetime', 'asc');
    }

    public function scopeIsPublished($query)
    {
        $now = Carbon::now();
        return $query
            ->where(function($q) use ($now) {
                $q->where('published_from', '<=', $now)
                  ->orWhereNull('published_from');
            })
            ->where(function($q) use ($now) {
                $q->where('published_to', '>=', $now)
                  ->orWhereNull('published_to');
            });
    }

    public function getTimezoneOptions()
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        return array_combine($timezones, $timezones);
    }
}
