<?php namespace Mercator\Calendar\Controllers;

use BackendMenu;
use Carbon\Carbon; 
use Backend\Classes\Controller;
use Mercator\Calendar\Models\CalendarEntry;
use Flash; 
class Calendars extends Controller
{
public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\RelationController::class 
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml'; 
    public $requiredPermissions = ['mercator.calendar.access_calendars'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Mercator.Calendar', 'calendar', 'calendars');
    }
    
    public function onLoadPurgeForm()
    {
        $this->vars['calendarId'] = post('record_id');
        // Set the default date to yesterday
        $this->vars['defaultPurgeDate'] = Carbon::yesterday()->format('Y-m-d');
        return $this->makePartial('purge_form');
    }
    
    public function onPurgeEntries()
    {
        $calendarId = post('calendar_id');
        $purgeDate = Carbon::parse(post('purge_date'));

        $count = CalendarEntry::where('calendar_id', $calendarId)
            ->where('start_datetime', '<', $purgeDate) // This line is now corrected
            ->delete();

        Flash::success("Successfully purged " . $count . " old entries.");

        // Find the model to refresh its relations
        $model = $this->formFindModelObject($calendarId);

        // Re-initialize BOTH the Form and Relation contexts
        $this->initForm($model);
        $this->initRelation($model);

        // Refresh the 'entries' relation partial on the page
        return $this->relationRefresh('entries');
    }
}
