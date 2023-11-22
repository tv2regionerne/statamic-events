<?php

namespace Tv2regionerne\StatamicEvents\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\ActionController;
use Tv2regionerne\StatamicEvents\Models\Handler;

class HandlerActionsController extends ActionController
{
    public function runAction(Request $request)
    {
        return parent::run($request);
    }

    public function bulkActionsList(Request $request)
    {
        return parent::bulkActions($request);
    }

    protected function getSelectedItems($items, $context)
    {
        return $items->map(fn ($item) => Handler::find($item));
    }
}
