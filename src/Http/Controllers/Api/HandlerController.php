<?php

namespace Tv2regionerne\StatamicEvents\Http\Controllers\Api;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Requests\FilteredRequest;
use Tv2regionerne\StatamicEvents\Facades\Drivers;
use Tv2regionerne\StatamicEvents\Http\Controllers\HandlerController as CpController;
use Tv2regionerne\StatamicEvents\Http\Requests\StoreRequest;
use Tv2regionerne\StatamicEvents\Http\Requests\UpdateRequest;
use Tv2regionerne\StatamicEvents\Models\Handler;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class HandlerController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index(FilteredRequest $request)
    {
        return (new CpController($request))->api($request);
    }

    public function show($id)
    {
        $handler = Handler::find($id);

        $this->abortIfInvalid($handler);
    }

    public function store(StoreRequest $request)
    {
        return (new CpController($request))->store($request);
    }

    public function update(UpdateRequest $request, $id)
    {
        $handler = Handler::find($id);

        $this->abortIfInvalid($handler);

        $driver = Drivers::all()->get($handler->driver) ?? Drivers::all()->first();

        $blueprint = (new CpController($request))->blueprint($driver->blueprintFields());

        $handlerData = collect($handler->toArray())->except(['updated_at', 'created_at', 'deleted_at', 'id']);

        // cp controller expects the full payload, so merge with existing values
        $mergedData = $this->mergeBlueprintAndRequestData($blueprint, $handlerData, $request);

        $request->merge($mergedData->all());

        return (new CpController($request))->update($request, $id);
    }

    public function destroy(Request $request, $id)
    {
        $handler = Handler::find($id);

        $this->abortIfInvalid($handler);

        $handler->delete();

        return response()->json(['error' => 'false']);
    }

    private function abortIfInvalid($handler)
    {
        if (! $handler) {
            response()->json(['error' => true, 'message' => 'Not found'], 404)->send();
        }
    }
}
