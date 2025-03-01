<?php

namespace WatheqAlshowaiter\ModelRequiredFields\Support;

use Illuminate\Support\Facades\App;

/**
 * Here are the shared logic across multiple files, now are ModelFieldsService & ModelRequiredFieldsServiceProvider
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
}
