<?php

namespace Tv2regionerne\StatamicEvents\Http\Controllers;

use Statamic\CP\Breadcrumbs;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Scope;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController as StatamicController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Tv2regionerne\StatamicEvents\Http\Requests\EditRequest;
use Tv2regionerne\StatamicEvents\Http\Requests\IndexRequest;
use Tv2regionerne\StatamicEvents\Http\Resources\ExecutionCollection;
use Tv2regionerne\StatamicEvents\Models\Execution;
use Tv2regionerne\StatamicEvents\Traits\PreparesModels;

class ExecutionController extends StatamicController
{
    use PreparesModels, QueriesFilters;

    public function index(IndexRequest $request)
    {
        $blueprint = $this->blueprint();

        $listingConfig = [
            'preferencesPrefix' => 'statamic-events.executions',
            'requestUrl' => cp_route('statamic-events.executions.listing-api'),
            'listingUrl' => cp_route('statamic-events.executions.index'),
        ];

        $columns = $blueprint->fields()->all()
            ->filter(fn (Field $field) => $field->isVisibleOnListing())
            ->map->handle()
            ->values();

        return view('statamic-events::executions.index', [
            'title' => __('Executions'),
            'recordCount' => Execution::count(),
            'primaryColumn' => 'handler',
            'columns' => $blueprint->columns()
                ->filter(fn ($column) => in_array($column->field, collect($columns)->pluck('handle')->toArray()))
                ->rejectUnlisted()
                ->values(),
            'filters' => Scope::filters('statamic-events.executions'),
            'listingConfig' => $listingConfig,
            'actionUrl' => cp_route('statamic-events.executions.actions.run'),
        ]);
    }

    public function api(FilteredRequest $request)
    {
        $blueprint = $this->blueprint();

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('order', 'asc');

        $query = Execution::query()
            ->orderBy($sortField, $sortDirection)
            ->when($search = $request->input('search'), fn ($query) => $query->where('event', 'like', "%{$search}%"));

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'blueprints' => [$blueprint],
        ]);

        $results = $query->paginate($request->input('perPage', config('statamic.cp.pagination_size')));

        return (new ExecutionCollection($results))
            ->setColumnPreferenceKey('statamic-events.executions.columns')
            ->blueprint($blueprint)
            ->additional([
                'meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ],
            ]);
    }

    public function show(EditRequest $request, $record)
    {
        $record = Execution::find($record);

        if (! $record) {
            throw new NotFoundHttpException();
        }

        $viewData = [
            'title' => __('View Executions'),
            'breadcrumbs' => (new Breadcrumbs([[
                'text' => __('Handlers'),
                'url' => cp_route('statamic-events.executions.index'),
            ]]))->toArray(),
            'record' => $record,
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-events::executions.show', $viewData);
    }

    private function blueprint(): Blueprint
    {
        return Blueprint::make()
            ->setHandle('statamic-events-executions')
            ->setContents([
                'tabs' => [
                    'main' => [
                        'sections' => [
                            [
                                'fields' => [
                                    'handler' => [
                                        'handle' => 'handler',
                                        'field' => [
                                            'display' => __('Handler'),
                                            'type' => 'text',
                                            'listable' => true,
                                        ],
                                    ],
                                    'event' => [
                                        'handle' => 'event',
                                        'field' => [
                                            'display' => __('Event'),
                                            'type' => 'text',
                                            'listable' => true,
                                        ],
                                    ],
                                    'status' => [
                                        'handle' => 'status',
                                        'field' => [
                                            'display' => __('Status'),
                                            'type' => 'text',
                                            'listable' => true,
                                        ],
                                    ],
                                    'created_at' => [
                                        'handle' => 'created_at',
                                        'field' => [
                                            'display' => __('Date'),
                                            'type' => 'date',
                                            'listable' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }
}
