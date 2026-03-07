<?php namespace Mercator\Calendar\Components;

use Cms\Classes\ComponentBase;
use Mercator\Calendar\Models\CalendarEntry;
use Url;

class Entry extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Calendar Entry',
            'description' => 'Displays a single calendar event.'
        ];
    }

    public function defineProperties()
    {
        return [
            'entryId' => [
                'title'       => 'mercator.calendar::lang.component.entry.entry',
                'description' => 'mercator.calendar::lang.component.entry.entry_description',
                'type'        => 'dropdown',
                'default'     => '{{ :entry_id }}',
                // compute options immediately so that the CMS builder can render a select
                'options'     => $this->getEntryIdOptions(),
            ],
            'showEventIcsButton' => [
                'title'       => 'mercator.calendar::lang.component.entry.show_ics',
                'description' => 'If checked an iCal download link will be rendered for the event.',
                'type'        => 'checkbox',
                'default'     => 1,
            ],
        ];
    }

    public function onRun()
    {
        $this->addCss('/plugins/mercator/calendar/assets/css/calendar-component.css');
        $this->prepareVars();
    }

    public function getEntryIdOptions()
    {
        $options = ['' => '-- Select an Entry --'];
        $entries = CalendarEntry::isPublished()->sorted()->get();
        foreach ($entries as $entry) {
            $options[$entry->id] = sprintf('%s (ID %s)', $entry->title, $entry->id);
        }
        return $options;
    }

    protected function prepareVars()
    {
        $entryId = trim((string) $this->property('entryId'));
        if (!$entryId) {
            return;
        }

        // always respect published scope so we don't show unpublished events
        $entry = CalendarEntry::isPublished()->find($entryId);
        if (!$entry) {
            return;
        }

        $this->page['entry'] = $entry;
        $this->page['showEventIcsButton'] = (bool) $this->property('showEventIcsButton');
        $this->page['icsUrl'] = Url::to('/mercator/calendar/ical/event/' . $entry->id . '.ics');
    }
}
