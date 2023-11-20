<?php

namespace Tv2regionerne\StatamicEvents\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Statamic\CP\Breadcrumbs;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Scope;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController as StatamicController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Http\Requests\CreateRequest;
use Tv2regionerne\StatamicEvents\Http\Requests\EditRequest;
use Tv2regionerne\StatamicEvents\Http\Requests\IndexRequest;
use Tv2regionerne\StatamicEvents\Http\Requests\StoreRequest;
use Tv2regionerne\StatamicEvents\Http\Requests\UpdateRequest;
use Tv2regionerne\StatamicEvents\Http\Resources\HandlerCollection;
use Tv2regionerne\StatamicEvents\Models\Handler;
use Tv2regionerne\StatamicEvents\Traits\PreparesModels;

class CpController extends StatamicController
{
    use PreparesModels, QueriesFilters;

    public function index(IndexRequest $request)
    {
        $blueprint = $this->blueprint();

        $listingConfig = [
            'preferencesPrefix' => 'statamic-events',
            'requestUrl' => cp_route('statamic-events.listing-api'),
            'listingUrl' => cp_route('statamic-events.index'),
        ];

        $columns = $blueprint->fields()->all()
            ->filter(fn (Field $field) => $field->isVisibleOnListing())
            ->map->handle()
            ->values();

        return view('statamic-events::index', [
            'title' => __('Event Handlers'),
            'recordCount' => Handler::count(),
            'primaryColumn' => 'title',
            'columns' => $blueprint->columns()
                ->filter(fn ($column) => in_array($column->field, collect($columns)->pluck('handle')->toArray()))
                ->rejectUnlisted()
                ->values(),
            'filters' => Scope::filters('statamic-events'),
            'listingConfig' => $listingConfig,
            'actionUrl' => cp_route('statamic-events.actions.run'),
            'createUrl' => cp_route('statamic-events.create'),
            'drivers' => Drivers::all()
                ->map(fn ($driver, $handle) => ['handle' => $handle, 'title' => $driver->title()])
                ->values()
                ->all(),
        ]);
    }

    public function api(FilteredRequest $request)
    {
        $blueprint = $this->blueprint();

        $sortField = $request->input('sort', 'title');
        $sortDirection = $request->input('order', 'asc');

        $query = Handler::query()
            ->orderBy($sortField, $sortDirection)
            ->when($search = $request->input('search'), fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('event', 'like', "%{$search}%"));


        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'blueprints' => [$blueprint],
        ]);

        $results = $query->paginate($request->input('perPage', config('statamic.cp.pagination_size')));

        return (new HandlerCollection($results))
            ->setColumnPreferenceKey('statamic-events.columns')
            ->blueprint($blueprint)
            ->additional([
                'meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ],
            ]);
    }

    public function create(CreateRequest $request)
    {
        $drivers = Drivers::all();

        $driver = ($handle = $request->input('blueprint')) ? ($drivers->get($handle) ? $handle : false) : false;

        if (! $driver) {
            $driver = $drivers->keys()->first();
        }

        $blueprint = $this->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        $fields = $fields->addValues([
            'driver' => $driver
        ]);

        $driverTitle = $drivers->get($driver)->title();

        $viewData = [
            'title' => __('Create :driver', ['driver' => $driverTitle]),
            'action' => cp_route('statamic-events.store'),
            'method' => 'POST',
            'breadcrumbs' => new Breadcrumbs([[
                'text' => __('Event Handlers'),
                'url' => cp_route('statamic-events.index'),
            ]]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'permalink' => null,
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-events::create', $viewData);
    }

    public function store(StoreRequest $request)
    {
        $blueprint = $this->blueprint();

        $blueprint
            ->fields()
            ->addValues($request->all())
            ->validator()
            ->validate();

        $model = Handler::make();

        $this->prepareModelForSaving($blueprint, $model, $request);

        $model->save();

        return [
            'data' => $this->getReturnData($model),
            'redirect' => cp_route('statamic-events.edit', [
                'record' => $model->getKey(),
            ]),
        ];
    }

    public function edit(EditRequest $request, $record)
    {
        $record = Handler::find($record);

        if (! $record) {
            throw new NotFoundHttpException();
        }

        $blueprint = $this->blueprint();

        $values = $this->prepareModelForPublishForm($blueprint, $record);

        $fields = $blueprint->fields()->addValues($values)->preProcess();

        $driverTitle = Drivers::all()->get($fields->get('driver')->value())->title();

        $viewData = [
            'title' => __('Edit :driver', ['driver' => $driverTitle]),
            'action' => cp_route('statamic-events.update', [
                'record' => $record->getKey(),
            ]),
            'method' => 'PATCH',
            'breadcrumbs' => new Breadcrumbs([[
                'text' => __('Event Handlers'),
                'url' => cp_route('statamic-events.index'),
            ]]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'currentRecord' => [
                'id' => $record->getKey(),
                'title' => $record->title,
                'edit_url' => $request->url(),
            ],
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-events::edit', $viewData);
    }

    public function update(UpdateRequest $request, $record)
    {
        $blueprint = $this->blueprint();

        $blueprint->fields()->addValues($request->all())->validator()->validate();

        $model = Handler::find($record);

        $this->prepareModelForSaving($blueprint, $model, $request);

        $model->save();

        return ['data' => $this->getReturnData($model)];
    }

    /**
     * Build an array with the correct return data for the inline publish forms.
     */
    protected function getReturnData(Model $record): array
    {
        return array_merge($record->toArray(), [
            'title' => $record->title,
            'edit_url' => cp_route('statamic-events.edit', [
                'record' => $record->getKey(),
            ]),
        ]);
    }

    private function blueprint(): Blueprint
    {
        return Blueprint::make()
            ->setHandle('statamic-events')
            ->setContents([
                'handle' => 'general',
                'fields' => [
                    'title' => [
                        'handle' => 'title',
                        'field' => [
                            'type' => 'text',
                            'listable' => 'listable',
                        ],
                    ],
                    'event' => [
                        'handle' => 'event',
                        'field' => [
                            'type' => 'text',
                            'listable' => 'listable',
                        ],
                    ],
                    'driver' => [
                        'handle' => 'driver',
                        'field' => [
                            'type' => 'hidden',
                            'listable' => 'listable',
                        ],
                    ],
                ],
            ]);
    }
}
