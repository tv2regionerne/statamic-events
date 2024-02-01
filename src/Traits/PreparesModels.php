<?php

namespace Tv2regionerne\StatamicEvents\Traits;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Section;

trait PreparesModels
{
    protected function prepareModelForPublishForm(Blueprint $blueprint, Model $model): array
    {
        return $blueprint->fields()->all()
            ->mapWithKeys(function (Field $field) use ($model) {
                $value = data_get($model, Str::replace('->', '.', $field->handle())) ?? data_get($model, 'config.'.Str::replace('->', '.', $field->handle()));

                // When $value is a Carbon instance, format it it with the format defined in the blueprint.
                if ($value instanceof CarbonInterface) {
                    $format = $field->get('format', 'Y-m-d H:i');

                    $value = $value->format($format);
                }

                // it makes more sense in the db to have should_queue, but in the UI 'blocking'
                if ($field->handle() == 'should_queue') {
                    $value = ! $value;
                }

                return [$field->handle() => $value];
            })
            ->toArray();
    }

    protected function prepareModelForSaving(Blueprint $blueprint, Model &$model, Request $request): void
    {
        $config = [];
        $blueprint->fields()->all()
            ->filter(fn (Field $field) => $this->shouldSaveField($field))
            ->each(function (Field $field) use (&$config, &$model, $request) {
                $processedValue = $field->fieldtype()->process($request->get($field->handle()));

                // Skip the field if it exists in the model's $appends array AND there's no mutator for it on the model.
                if (in_array($field->handle(), $model->getAppends(), true) && ! $model->hasSetMutator($field->handle()) && ! $model->hasAttributeSetMutator($field->handle())) {
                    return;
                }

                $handle = $field->handle();

                if (in_array($handle, ['id', 'title', 'driver', 'events', 'should_queue', 'enabled', 'throw_exception_on_fail'])) {
                    $model->setAttribute($handle, $processedValue);

                    return;
                }

                Arr::set($config, str_replace('->', '', $handle), $processedValue);
            });

        $model->config = $config;

        // it makes more sense in the db to have should_queue, but in the UI 'blocking'
        $model->should_queue = ! $model->should_queue;
    }

    protected function shouldSaveField(Field $field): bool
    {
        if ($field->fieldtype() instanceof Section) {
            return false;
        }

        if ($field->visibility() === 'computed') {
            return false;
        }

        if ($field->get('save', true) === false) {
            return false;
        }

        return true;
    }
}
