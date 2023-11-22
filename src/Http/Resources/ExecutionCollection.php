<?php

namespace Tv2regionerne\StatamicEvents\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection as LaravelResourceCollection;
use Statamic\Facades\Action;
use Statamic\Facades\User;
use Tv2regionerne\StatamicEvents\Facades\Drivers;

class ExecutionCollection extends LaravelResourceCollection
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

        $columns = $this->columns->pluck('field')->toArray();

        return [
            'data' => $this->collection->map(function ($record) use ($columns) {
                $row = [];

                foreach ($this->blueprint->fields()->all() as $fieldHandle => $field) {
                    $key = str_replace('->', '.', $fieldHandle);

                    if ($fieldHandle == 'handler') {
                        $row[$fieldHandle] = $record->handler->title;
                        continue;
                    }

                    $row[$fieldHandle] = $field->setValue(data_get($record, $key))->preProcessIndex()->value();
                }

                $row['id'] = $record->getKey();
                $row['edit_url'] = null;
                $row['permalink'] = cp_route('statamic-events.executions.show', ['record' => $record->getRouteKey()]);
                $row['editable'] = false;
                $row['viewable'] = User::current()->can('view statamic events');
                $row['actions'] = Action::for($record);

                return $row;
            }),
            'meta' => ['columns' => $this->columns],
        ];
    }
}
