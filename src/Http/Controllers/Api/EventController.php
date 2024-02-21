<?php

namespace Tv2regionerne\StatamicEvents\Http\Controllers\Api;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\API\ApiController;
use Tv2regionerne\StatamicEvents\Http\Controllers\HandlerController as CpController;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class EventController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index(Request $request)
    {
        return [
            'data' => (new CpController($request))->buildEventsList(),
        ];
    }
}
