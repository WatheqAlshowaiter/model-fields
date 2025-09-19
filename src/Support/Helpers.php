<?php

namespace WatheqAlshowaiter\ModelFields\Support;

use Illuminate\Support\Facades\App;

/**
 * Here are the shared logic across multiple files, now are ModelFieldsService & ModelFieldsServiceProvider
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
     * @return bool
     */
    public static function isLaravelVersionLessThanOrEqualTo10()
    {
        return version_compare(App::version(), '11.0', '<');
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
