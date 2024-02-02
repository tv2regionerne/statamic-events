<?php

namespace Tv2regionerne\StatamicEvents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Execution extends Model
{
    use HasFactory;

    public $table = 'event_handler_executions';

    protected $casts = [];
    protected $guarded = [];

    public function complete(string $output = '')
    {
        $this->log(__('Complete'), ['output' => $output]);

        $this->output = $output;
        $this->status = 'completed';
        $this->save();
    }

    public function fail(string $output = '')
    {
        $this->log(__('Failed'), ['output' => $output]);

        $this->output = $output;
        $this->status = 'failed';
        $this->save();

        if ($handler = $this->handler) {
            if ($handler->throw_exception_on_fail && ! $handler->should_queue) {
                throw new \Exception('Failed: '.$output);
            }
        }
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(Handler::class);
    }

    public function log(string $message, array $extra = [])
    {
        if (! auth()->user()) {
            return;
        }

        return activity('statamic-events')
            ->performedOn($this)
            ->withProperties($extra)
            ->log($message);
    }

    public function logs(): Builder
    {
        $class = config('activitylog.activity_model');

        return app($class)::inLog('statamic-events')
            ->whereSubjectId($this->getKey())
            ->whereSubjectType(get_class($this));
    }

    protected function input(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => unserialize($value),
            set: fn ($value) => serialize($value),
        );
    }
}
