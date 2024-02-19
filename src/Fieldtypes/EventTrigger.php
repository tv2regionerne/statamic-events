<?php

namespace Tv2regionerne\StatamicEvents\FieldTypes;

use Statamic\Entries\Collection;
use Statamic\Fields\Fieldtype;

class EventTrigger extends Fieldtype
{
    protected $component = 'statamic-events-trigger-button';

    public function icon()
    {
        return 'add-circle';
    }

    public function preload()
    {
        if (! $entry = $this->field->parent()) {
            return [];
        }

        // when it's a new entry the "parent" is the collection
        // in this case, we can't show it anyway so return empty
        if ($entry instanceof Collection) {
            return [];
        }

        return ['entry_id' => $entry->id(), 'post_url' => cp_route('statamic-events.trigger')];
    }
}
