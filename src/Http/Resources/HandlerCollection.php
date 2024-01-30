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

        $columns = $this->columns->pluck('field')->toArray();

        return [
            'data' => $this->collection->map(function ($record) use ($columns) {
                $row = [];

                foreach ($this->blueprint->fields()->all() as $fieldHandle => $field) {
                    $key = str_replace('->', '.', $fieldHandle);

                    $row[$fieldHandle] = $field->setValue(data_get($record, $key))->preProcessIndex()->value();

                    if ($fieldHandle == 'should_queue') {
                        $row[$fieldHandle] = ! $row[$fieldHandle];
                    }
                }

                $row['driver'] = Blink::once('statamic-events::drivers', fn () => Drivers::all())->get($row['driver'])?->title();

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
