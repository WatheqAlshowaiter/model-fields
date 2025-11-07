<?php

namespace WatheqAlshowaiter\ModelFields\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;

/**
 * Here are the shared logic across multiple files, now are FieldsService & ModelFieldsServiceProvider
 */
class Helpers
{
    /**
     * @return bool
     */
    public static function isLaravelVersionLessThan10()
    {
        return version_compare(App::version(), '10.0', '<');
    }

    /**
     * @return string[]
     */
    public static function getModelDefaultAttributes($model)
    {
        return array_keys((new $model)->getAttributes());
    }

    public static function getTableFromThisModel($model)
    {
        $table = (new $model)->getTable();

        return str_replace('.', '__', $table);
    }

    /**
     * Get fields that are automatically filled by model observers/events
     * during 'creating' and 'saving' events
     *
     * @param  class-string  $model
     * @return string[]
     */
    public static function getObserverFilledFields($modelOrClass)
    {
        if ($modelOrClass instanceof Model) {
            $model = $modelOrClass->newInstance();   // fresh instance of same model
            $modelClass = get_class($modelOrClass);
        } else {
            $model = new $modelOrClass;
            $modelClass = $modelOrClass;
        }

        // ensure clean baseline
        $model->syncOriginal();

        // fire the creating events (observer + model booted events)
        Event::dispatch("eloquent.creating: {$modelClass}", $model);
        Event::dispatch("eloquent.saving: {$modelClass}", $model);

        $dirty = $model->getDirty();
        $dirtyNoNull = array_filter($dirty); // exclude null values

        return array_keys($dirtyNoNull);
    }
}
