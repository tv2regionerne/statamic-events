<?php

namespace Tv2regionerne\StatamicEvents\Scopes;

use Illuminate\Support\Carbon;
use Statamic\Query\Scopes\Filter;
use Statamic\Support\Arr;
use Tv2regionerne\StatamicEvents\Models\Handler as HandlerModel;

class Handler extends Filter
{
    protected $pinned = true;

    public static function title()
    {
        return __('Handler');
    }

    public function fieldItems()
    {
        return [
            'handler' => [
                'type' => 'select',
                'placeholder' => __('Select Handler'),
                'options' => HandlerModel::enabled()
                    ->orderBy('title')
                    ->get()
                    ->mapWithKeys(fn ($handler) => [$handler->getKey() => $handler->title]),
            ],
        ];
    }

    public function apply($query, $values)
    {
        $query->whereHandlerId($values['handler']);
    }

    public function badge($values)
    {
        $handler = HandlerModel::find($values['handler']);
        return  __('Handler: :handler', ['handler' => $handler->title]);
    }

    public function visibleTo($key)
    {
        return in_array($key, ['statamic-events.executions']);
    }
}
