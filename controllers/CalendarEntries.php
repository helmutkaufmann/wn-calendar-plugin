<?php namespace Mercator\Calendar\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

class CalendarEntries extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class
    ];
    
    /**
     * @var string Configuration file for the `ListController` behavior.
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var string Configuration file for the `FormController` behavior.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var array Permissions required to view this page.
     */
    public $requiredPermissions = ['mercator.calendar.access_entries'];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Mercator.Calendar', 'calendar', 'entries');
    }

    /**
     * Add this method to explicitly call the FormController behavior's create action.
     */
    public function create()
    {
        return $this->asExtension('FormController')->create();
    }

    /**
     * Add this method to explicitly call the FormController behavior's update action.
     */
    public function update($recordId)
    {
        return $this->asExtension('FormController')->update($recordId);
    }
}
