<?php

namespace WatheqAlshowaiter\ModelFields\Support;

use Illuminate\Database\Eloquent\Model;
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
    public static function getObserverFilledFields($model)
    {
        /** @var Model $modelInstance */
        $modelInstance = new $model;

        // Get attributes before firing events
        $attributesBeforeEvents = array_keys($modelInstance->getAttributes());

        // Fire the creating and saving events to trigger observers
        $modelInstance->fireModelEvent('creating', false);
        $modelInstance->fireModelEvent('saving', false);

        // Get attributes after firing events
        $attributesAfterEvents = array_keys($modelInstance->getAttributes());

        // Get all database fields
        $allFields = self::getAllFieldsForModel($model);

        // Return only the new fields that were added by observers/events
        // and are actual database fields
        return collect($attributesAfterEvents)
            ->diff($attributesBeforeEvents)
            ->filter(function ($field) use ($allFields) {
                return in_array($field, $allFields);
            })
            ->values()
            ->toArray();
    }

    /**
     * Helper to get all fields for a model (used internally)
     *
     * @param  class-string  $model
     * @return string[]
     */
    private static function getAllFieldsForModel($model)
    {
        if (self::isLaravelVersionLessThan10()) {
            // For older versions, we need to query the database
            $table = self::getTableFromThisModel($model);
            $databaseDriver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

            switch ($databaseDriver) {
                case 'sqlite':
                    $queryResult = \Illuminate\Support\Facades\DB::select("PRAGMA table_info($table)");

                    return collect($queryResult)
                        ->map(fn ($column) => (array) $column)
                        ->pluck('name')
                        ->toArray();
                case 'mysql':
                case 'mariadb':
                    $queryResult = \Illuminate\Support\Facades\DB::select(
                        'SELECT COLUMN_NAME AS name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION ASC',
                        [$table]
                    );

                    return collect($queryResult)
                        ->map(fn ($column) => (array) $column)
                        ->pluck('name')
                        ->toArray();
                case 'pgsql':
                    $queryResult = \Illuminate\Support\Facades\DB::select(
                        'SELECT column_name AS name FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position ASC',
                        [$table]
                    );

                    return collect($queryResult)
                        ->map(fn ($column) => (array) $column)
                        ->pluck('name')
                        ->toArray();
                case 'sqlsrv':
                    $queryResult = \Illuminate\Support\Facades\DB::select(
                        'SELECT COLUMN_NAME AS name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION ASC',
                        [$table]
                    );

                    return collect($queryResult)
                        ->map(fn ($column) => (array) $column)
                        ->pluck('name')
                        ->toArray();
                default:
                    return [];
            }
        }

        $table = self::getTableFromThisModel($model);

        return collect(\Illuminate\Support\Facades\Schema::getColumns($table))
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }
}
