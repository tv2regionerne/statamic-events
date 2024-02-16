<?php

namespace Tv2regionerne\StatamicEvents\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection as LaravelResourceCollection;
use Statamic\Facades\Action;
use Statamic\Facades\Blink;
use Statamic\Facades\User;
use Tv2regionerne\StatamicEvents\Facades\Drivers;

class HandlerCollection extends LaravelResourceCollection
{
    public $collects;

    public $columns;
    protected $blueprint;
    protected $columnPreferenceKey;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function setColumnPreferenceKey($key): self
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    public function setColumns(): self
    {
        $columns = $this->blueprint->columns();

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();

        return $this;
    }

    public function toArray($request): array
    {
        $this->setColumns();

        return [
            'data' => $this->collection->map(function ($record) {
                $row = [];

                $columns = collect($record->getAttributes())->keys();

                foreach ($this->blueprint->fields()->all() as $fieldHandle => $field) {
                    $key = str_replace('->', '.', $fieldHandle);

                    if (! str_contains($key, '.')) {
                        if (! $columns->contains($key)) {
                            $key = 'config.'.$key;
                        }
                    }

                    if ($key == 'driver') {
                        $row[$fieldHandle] = data_get($record, $key);
                    } else {
                        $row[$fieldHandle] = $field->setValue(data_get($record, $key))->preProcessIndex()->value();
                    }

                    if ($fieldHandle == 'should_queue') {
                        $row[$fieldHandle] = ! $row[$fieldHandle];
                    }
                }

                $row['id'] = $record->getKey();
                $row['edit_url'] = cp_route('statamic-events.handlers.edit', ['record' => $record->getRouteKey()]);
                $row['permalink'] = null;
                $row['editable'] = User::current()->can('edit statamic events');
                $row['viewable'] = User::current()->can('view statamic events');
                $row['actions'] = Action::for($record);

                return $row;
            }),
            'meta' => ['columns' => $this->columns],
        ];
    }
}
