<?php

namespace Tv2regionerne\StatamicEvents\Scopes;

use Illuminate\Support\Carbon;
use Statamic\Query\Scopes\Filter;
use Statamic\Support\Arr;

class Date extends Filter
{
    protected $pinned = true;

    public static function title()
    {
        return __('Date');
    }

    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '<' => __('Before'),
                    '>' => __('After'),
                    'between' => __('Between'),
                ],
            ],
            'value' => [
                'type' => 'date',
                'inline' => true,
                'full_width' => true,
                'if' => [
                    'operator' => 'contains_any >, <',
                ],
                'required' => false,
            ],
            'range_value' => [
                'type' => 'date',
                'inline' => true,
                'mode' => 'range',
                'full_width' => true,
                'if' => [
                    'operator' => 'between',
                ],
                'required' => false,
            ],
        ];
    }

    public function apply($query, $values)
    {
        $operator = $values['operator'];
        $handle = 'created_at';

        if ($operator == 'between') {
            $query->whereDate($handle, '>=', Carbon::parse($values['range_value']['start']));
            $query->whereDate($handle, '<=', Carbon::parse($values['range_value']['end']));

            return;
        }

        $value = Carbon::parse($values['value']['date']);

        $query->where($handle, $operator, $value);
    }

    public function badge($values)
    {
        $field = __('Date');
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

        if ($operator == 'between') {
            return $field.' '.strtolower($translatedOperator).' '.$values['range_value']['start'].' '.__('and').' '.$values['range_value']['end'];
        }

        return $field.' '.strtolower($translatedOperator).' '.$values['value']['date'];
    }

    public function visibleTo($key)
    {
        return in_array($key, ['statamic-events.executions']);
    }
}
