<?php

namespace Tv2regionerne\StatamicEvents\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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

class HandlerController extends StatamicController
{
    use PreparesModels, QueriesFilters;

    public function index(IndexRequest $request)
    {
        $blueprint = $this->blueprint();

        $blueprint = $this->ensureAllListableFields($blueprint);

        $listingConfig = [
            'preferencesPrefix' => 'statamic-events',
            'requestUrl' => cp_route('statamic-events.handlers.listing-api'),
            'listingUrl' => cp_route('statamic-events.handlers.index'),
        ];

        $columns = $blueprint->fields()->all()
            ->filter(fn (Field $field) => $field->isVisibleOnListing())
            ->map->handle()
            ->values();

        return view('statamic-events::handlers.index', [
            'title' => __('Handlers'),
            'recordCount' => Handler::count(),
            'primaryColumn' => 'title',
            'columns' => $blueprint->columns()
                ->filter(fn ($column) => in_array($column->field, collect($columns)->pluck('handle')->toArray()))
                ->rejectUnlisted()
                ->values(),
            'filters' => Scope::filters('statamic-events'),
            'listingConfig' => $listingConfig,
            'actionUrl' => cp_route('statamic-events.handlers.actions.run'),
            'createUrl' => cp_route('statamic-events.handlers.create'),
            'drivers' => Drivers::all()
                ->map(fn ($driver, $handle) => ['handle' => $handle, 'title' => $driver->title()])
                ->values()
                ->all(),
        ]);
    }

    public function api(FilteredRequest $request)
    {
        $blueprint = $this->blueprint();

        $blueprint = $this->ensureAllListableFields($blueprint);

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

        $blueprint = $this->blueprint($drivers->get($driver)->blueprintFields());
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        $driverTitle = $drivers->get($driver)->title();

        $viewData = [
            'title' => __('Create :driver', ['driver' => $driverTitle]),
            'action' => cp_route('statamic-events.handlers.store'),
            'method' => 'POST',
            'breadcrumbs' => new Breadcrumbs([[
                'text' => __('Handlers'),
                'url' => cp_route('statamic-events.handlers.index'),
            ]]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'permalink' => null,
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-events::handlers.create', $viewData);
    }

    public function store(StoreRequest $request)
    {
        $drivers = Drivers::all();

        $driver = ($handle = $request->input('driver')) ? ($drivers->get($handle) ? $handle : false) : false;

        if (! $driver) {
            $driver = $drivers->keys()->first();
        }

        $blueprint = $this->blueprint($drivers->get($driver)->blueprintFields());

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
            'redirect' => cp_route('statamic-events.handlers.edit', [
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

        $driver = Drivers::all()->get($record->driver) ?? Drivers::all()->first();

        $blueprint = $this->blueprint($driver->blueprintFields());

        $values = $this->prepareModelForPublishForm($blueprint, $record);

        $fields = $blueprint->fields()->addValues($values)->preProcess();

        $viewData = [
            'title' => __('Edit :driver', ['driver' => $record->title]),
            'action' => cp_route('statamic-events.handlers.update', [
                'record' => $record->getKey(),
            ]),
            'method' => 'PATCH',
            'breadcrumbs' => new Breadcrumbs([[
                'text' => __('Handlers'),
                'url' => cp_route('statamic-events.handlers.index'),
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

        return view('statamic-events::handlers.edit', $viewData);
    }

    public function update(UpdateRequest $request, $record)
    {
        $driver = Drivers::all()->get($request->input('driver')) ?? Drivers::all()->first();

        $blueprint = $this->blueprint($driver->blueprintFields());

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
            'edit_url' => cp_route('statamic-events.handlers.edit', [
                'record' => $record->getKey(),
            ]),
        ]);
    }

    private function blueprint(?array $fields = []): Blueprint
    {
        $fields = $this->ensureFieldsAreTabbed($fields);

        return Blueprint::make()
            ->setHandle('statamic-events')
            ->setContents($fields)
            ->ensureFieldsInTab([
                'driver' => [
                    'display' => __('Driver'),
                    'handle' => 'driver',
                    'type' => 'hidden',
                    'listable' => 'listable',
                    'default' => request()->input('blueprint') ?? '',
                ],
                'events' => [
                    'display' => __('Events'),
                    'handle' => 'events',
                    'type' => 'select',
                    'listable' => 'listable',
                    'required' => true,
                    'taggable' => true,
                    'options' => $this->buildEventsList(),
                    'multiple' => true,
                ],
                'title' => [
                    'display' => __('Title'),
                    'handle' => 'title',
                    'type' => 'text',
                    'listable' => 'listable',
                    'required' => true,
                ],
            ], 'main', true)
            ->ensureFieldsInTab([
                'enabled' => [
                    'display' => __('Enabled'),
                    'handle' => 'enabled',
                    'type' => 'toggle',
                    'listable' => 'listable',
                    'default' => false,
                    'required' => true,
                ],
                'should_queue' => [
                    'display' => __('Blocking'),
                    'handle' => 'enabled',
                    'type' => 'toggle',
                    'listable' => 'listable',
                    'default' => true,
                    'required' => true,
                ],
            ], 'sidebar', true);
    }

    private function buildEventsList()
    {
        return Cache::remember('statamic-events::event-list', 10000000, function () {
            return collect(config('statamic-events.events'))
                ->mapWithKeys(function ($folder, $namespace) {
                    return collect(glob(base_path($folder.'/*.php')))
                        ->mapWithKeys(function ($file) use ($namespace) {
                            $fqcn = $namespace.'\\'.Str::of($file)->after('/src/')->before('.php')->replace('/', '\\');

                            return [$fqcn => $fqcn];
                        })
                        ->all();
                })
                ->all();
        });
    }

    private function ensureAllListableFields($blueprint)
    {
        $fields = Drivers::all()->map(function ($driver, $handle) use ($blueprint) {
            $driverFields = $this->ensureFieldsAreTabbed($driver->blueprintFields());

            $fields = Blueprint::make()
                ->setHandle('statamic-events-'.$handle)
                ->setContents($driverFields)
                ->fields()
                ->all()
                ->filter(fn (Field $field) => $field->isVisibleOnListing() || $field->get('listable', '') == 'hidden')
                ->each(fn ($field) => $blueprint->ensureField($field->handle(), $field->config()));
        });

        return $blueprint;
    }

    private function ensureFieldsAreTabbed(array $fields)
    {
        if (! isset($fields['tabs'])) {
            $fields = [
                'tabs' => [
                    'main' => [
                        'display' => __('main'),
                        'sections' => $this->ensureFieldsAreSectioned($fields),
                    ],
                ],
            ];
        }

        return $fields;
    }

    private function ensureFieldsAreSectioned(array $fields)
    {
        if (! isset($fields['sections'])) {
            $fields = [
                [
                    'fields' => $fields,
                ],
            ];
        }

        return $fields;
    }
}
