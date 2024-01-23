<?php

namespace Tv2regionerne\StatamicEvents\Actions;

use Statamic\Actions\Action;
use Tv2regionerne\StatamicEvents\Models\Handler;

class DeleteHandler extends Action
{
    protected $dangerous = true;

    public static function title()
    {
        return __('Delete');
    }

    public function visibleTo($item)
    {
        return $item instanceof Handler;
    }

    public function visibleToBulk($items)
    {
        return $items
            ->map(fn ($item) => $this->visibleTo($item))
            ->filter(fn ($isVisible) => $isVisible === true)
            ->count() === $items->count();
    }

    public function authorize($user, $item)
    {
        return $user->can('delete statamic events');
    }

    public function buttonText()
    {
        /* @translation */
        return 'Delete|Delete :count items?';
    }

    public function confirmationText()
    {
        /* @translation */
        return 'Are you sure you want to want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function run($items, $values)
    {
        $items->each->delete();
    }
}
