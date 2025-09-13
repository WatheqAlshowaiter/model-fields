<?php

namespace WatheqAlshowaiter\ModelRequiredFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use WatheqAlshowaiter\ModelRequiredFields\Exceptions\InvalidModelClassException;
use WatheqAlshowaiter\ModelRequiredFields\Exceptions\UnsupportedDatabaseDriverException;
use WatheqAlshowaiter\ModelRequiredFields\Support\Helpers;

class FieldsService
{
    /**
     * @var class-string<Model>
     */
    protected $modelClass;

    /**
     * Set up the model class to get fields from
     *
     * @param  class-string<Model>  $modelClass
     * @return $this
     */
    public function model($modelClass)
    {
        if (! $this->isEloquentModelClass($modelClass)) {
            throw new InvalidModelClassException('Model class must be an instance of Eloquent model');
        }

        $this->modelClass = $modelClass;

        return $this;
    }

    public function allFields()
    {
        $this->throwIfNotUsingModelMethodFirst();

        if (Helpers::isLaravelVersionLessThan10()) {
            return $this->allFieldsForOlderVersions();
        }

        return collect(Schema::getColumns($this->getTableFromModel()))
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get the required fields that without it we will have a SQL error, not primary,
     * no nullables, no database or application defaults
     *
     * @return string[]
     */
    public function requiredFields()
    {
        $this->throwIfNotUsingModelMethodFirst();

        if (Helpers::isLaravelVersionLessThan10()) {
            return $this->requiredFieldsForOlderVersions();
        }

        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $primaryIndex = $this->getPrimaryField();

        return collect(Schema::getColumns($this->getTableFromModel()))
            ->map(function ($column) { // specific to mariadb
                if ($column['default'] == 'NULL') {
                    $column['default'] = null;
                }

                return $column;
            })
            ->reject(function ($column) use ($primaryIndex) {
                return
                    $column['nullable'] ||
                    $column['default'] != null ||
                    (in_array($column['name'], $primaryIndex));
            })
            ->reject(function ($column) use ($modelDefaultAttributes) {
                return in_array($column['name'], $modelDefaultAttributes);
            })
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get the fields that can be nullable when creating the model
     *
     * @return string[]
     */
    public function nullableFields()
    {
        $this->throwIfNotUsingModelMethodFirst();

        if (Helpers::isLaravelVersionLessThan10()) {
            return $this->nullableFieldsForOlderVersions();
        }

        return collect(Schema::getColumns($this->getTableFromModel()))
            ->map(function ($column) { // specific to mariadb
                if ($column['default'] == 'NULL') {
                    $column['default'] = null;
                }

                return $column;
            })
            ->filter(function ($column) {
                return $column['nullable'];
            })
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * @return string[]
     * todo thinking what is better to return ['id'] or 'id'?
     */
    public function primaryField()
    {
        $this->throwIfNotUsingModelMethodFirst();

        if (Helpers::isLaravelVersionLessThan10()) {
            return $this->primaryFieldForOlderVersions();
        }

        return collect(Schema::getIndexes($this->getTableFromModel()))
            ->filter(function ($index) {
                return $index['primary'];
            })
            ->pluck('columns')
            ->flatten()
            ->toArray();
    }

    /**
     * @return string[]
     */
    public function nullableFieldsForOlderVersions()
    {
        $this->throwIfNotUsingModelMethodFirst();

        $databaseDriver = DB::connection()->getDriverName();

        switch ($databaseDriver) {
            case 'sqlite':
                return $this->nullableFieldsForSqlite();
            case 'mysql':
            case 'mariadb':
                return $this->nullableFieldsForMysqlAndMariaDb();
            case 'pgsql':
                return $this->nullableFieldsForPostgres();
            case 'sqlsrv':
                return $this->nullableFieldsForSqlServer();
            default:
                throw new UnsupportedDatabaseDriverException('Unsupported database driver.');
        }
    }

    /**
     * @return string[]
     */
    public function primaryFieldForOlderVersions()
    {
        $this->throwIfNotUsingModelMethodFirst();

        $databaseDriver = DB::connection()->getDriverName();

        //todo until here
        switch ($databaseDriver) {
            case 'sqlite':
                return $this->primaryFieldForSqlite();
            case 'mysql':
            case 'mariadb':
            return $this->primaryFieldForMysqlAndMariaDb();
            case 'pgsql':
                return $this->primaryFieldForPostgres();
            case 'sqlsrv':
                return $this->primaryFieldForSqlServer();
            default:
                throw new UnsupportedDatabaseDriverException('Unsupported database driver.');
        }
    }

    /**
     * Get the required fields for the old version when not supported from schema
     * So we need to write an individual query for each SQL database
     *
     * @return string[]
     */
    public function requiredFieldsForOlderVersions()
    {
        $this->throwIfNotUsingModelMethodFirst();

        $databaseDriver = DB::connection()->getDriverName();

        switch ($databaseDriver) {
            case 'sqlite':
                return $this->requiredFieldsForSqlite();
            case 'mysql':
            case 'mariadb':
                return $this->requiredFieldsForMysqlAndMariaDb();
            case 'pgsql':
                return $this->requiredFieldsForPostgres();
            case 'sqlsrv':
                return $this->requiredFieldsForSqlServer();
            default:
                throw new UnsupportedDatabaseDriverException('Unsupported database driver.');
        }
    }

    /**
     * @return string[]
     */
    public function allFieldsForOlderVersions()
    {
        $this->throwIfNotUsingModelMethodFirst();

        $databaseDriver = DB::connection()->getDriverName();

        switch ($databaseDriver) {
            case 'sqlite':
                return $this->allFieldsForSqlite();
            case 'mysql':
            case 'mariadb':
                return $this->allFieldsForMysqlAndMariaDb();
            case 'pgsql':
                return $this->allFieldsForPostgres();
            case 'sqlsrv':
                return $this->allFieldsForSqlServer();
            default:
                throw new UnsupportedDatabaseDriverException('Unsupported database driver.');
        }
    }


    /**
     * Get the required fields that without it we will have a SQL error, not primary,
     * no nullables, no database or application defaults
     *
     * @return string[]
     */
    public function getRequiredFields(
        $withNullables = false,
        $withDefaults = false,
        $withPrimaryKey = false
    ) {
        $this->throwIfNotUsingModelMethodFirst();

        if (Helpers::isLaravelVersionLessThan10()) {
            return $this->getRequiredFieldsForOlderVersions(
                $withNullables,
                $withDefaults,
                $withPrimaryKey
            );
        }

        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $primaryIndex = $this->getPrimaryField();

        return collect(Schema::getColumns($this->getTableFromModel()))
            ->map(function ($column) { // specific to mariadb
                if ($column['default'] == 'NULL') {
                    $column['default'] = null;
                }

                return $column;
            })
            ->reject(function ($column) use ($primaryIndex, $withNullables, $withDefaults) {
                return
                    $column['nullable'] && ! $withNullables ||
                    $column['default'] != null && ! $withDefaults ||
                    (in_array($column['name'], $primaryIndex));
            })
            ->reject(function ($column) use ($modelDefaultAttributes, $withDefaults) {
                return in_array($column['name'], $modelDefaultAttributes) && ! $withDefaults;
            })
            ->pluck('name')
            ->when($withPrimaryKey, function ($collection) use ($primaryIndex) {
                return $collection->prepend(...$primaryIndex);
            })
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get the required fields for the old version when not supported from schema
     * So we need to write an individual query for each SQL database
     *
     * @param  $withNullables  = false
     * @param  $withDefaults  = false
     * @param  $withPrimaryKey  = false
     * @return string[]
     *
     * @deprecated
     */
    public function getRequiredFieldsForOlderVersions(
        $withNullables = false,
        $withDefaults = false,
        $withPrimaryKey = false
    ) {
        $this->throwIfNotUsingModelMethodFirst();

        $databaseDriver = DB::connection()->getDriverName();

        switch ($databaseDriver) {
            case 'sqlite':
                return $this->getRequiredFieldsForSqlite(
                    $withNullables,
                    $withDefaults,
                    $withPrimaryKey
                );
            case 'mysql':
            case 'mariadb':
                return $this->getRequiredFieldsForMysqlAndMariaDb(
                    $withNullables,
                    $withDefaults,
                    $withPrimaryKey
                );
            case 'pgsql':
                return $this->getRequiredFieldsForPostgres(
                    $withNullables,
                    $withDefaults,
                    $withPrimaryKey
                );
            case 'sqlsrv':
                return $this->getRequiredFieldsForSqlServer(
                    $withNullables,
                    $withDefaults,
                    $withPrimaryKey
                );
            default:
                throw new UnsupportedDatabaseDriverException('Unsupported database driver.');
        }
    }

    /**
     * @return string[]
     */
    protected function requiredFieldsForSqlite()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);
        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $queryResult = DB::select(/** @lang SQLite */ "PRAGMA table_info($table)");

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->reject(function ($column) {
                return $column['pk']
                    || $column['dflt_value'] != null
                    || ! $column['notnull'];
            })
            ->reject(function ($column) use ($modelDefaultAttributes) {
                return in_array($column['name'], $modelDefaultAttributes);
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function primaryFieldForSqlite()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $queryResult = DB::select(/** @lang SQLite */ "PRAGMA table_info($table)");

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->filter(function ($column) {
                return $column['pk'];
            })
            ->pluck('name')
            ->toArray();
    }


    /**
     * @return string[]
     */
    protected function allFieldsForSqlite()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $queryResult = DB::select(/** @lang SQLite */ "PRAGMA table_info($table)");

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->pluck('name')
            ->toArray();
    }


    /**
     * @return string[]
     */
    protected function nullableFieldsForSqlite()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $queryResult = DB::select(/** @lang SQLite */ "PRAGMA table_info($table)");

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->filter(function ($column) {
                return ! $column['notnull'];
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return string[]
     */
    public function getRequiredFieldsWithNullables()
    {
        $this->throwIfNotUsingModelMethodFirst();

        return $this->getRequiredFields($withNullables = true, $withDefaults = false, $withPrimaryKey = false);
    }

    /**
     * @return string[]
     */
    public function getRequiredFieldsWithDefaults()
    {
        $this->throwIfNotUsingModelMethodFirst();

        return $this->getRequiredFields($withNullables = false, $withDefaults = true, $withPrimaryKey = false);
    }

    /**
     * @return string[]
     */
    public function getRequiredFieldsWithPrimaryKey()
    {
        $this->throwIfNotUsingModelMethodFirst();

        return $this->getRequiredFields($withNullables = false, $withDefaults = false, $withPrimaryKey = true);
    }

    /**
     * @return string[]
     */
    public function getRequiredFieldsWithDefaultsAndPrimaryKey()
    {
        $this->throwIfNotUsingModelMethodFirst();

        return $this->getRequiredFields($withNullables = false, $withDefaults = true, $withPrimaryKey = true);
    }

    /**
     * @return string[]
     */
    public function getRequiredFieldsWithNullablesAndDefaults()
    {
        $this->throwIfNotUsingModelMethodFirst();

        return $this->getRequiredFields($withNullables = true, $withDefaults = true, $withPrimaryKey = false);
    }

    /**
     * @return string[]
     */
    public function getRequiredFieldsWithNullablesAndPrimaryKey()
    {
        $this->throwIfNotUsingModelMethodFirst();

        return $this->getRequiredFields($withNullables = true, $withDefaults = false, $withPrimaryKey = true);
    }

    /**
     * Get all fields of the model
     *
     * @return string[] $array
     */
    public function getAllFields()
    {
        $this->throwIfNotUsingModelMethodFirst();

        return $this->getRequiredFields(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = true
        );
    }

    /**
     * Get the primary field for the table
     *
     * @return string[]
     */
    public function getPrimaryField()
    {
        $this->throwIfNotUsingModelMethodFirst();

        $modelTable = $this->getTableFromModel();

        return collect(Schema::getIndexes($modelTable))
            ->filter(function ($index) {
                return $index['primary'];
            })
            ->pluck('columns')
            ->flatten()
            ->toArray();
    }

    /**
     * @return bool
     */
    protected function isEloquentModelClass($modelClass)
    {
        return is_a($modelClass, Model::class, true);
    }

    /**
     * @return string[]
     */
    protected function getRequiredFieldsForSqlite(
        $withNullables = false,
        $withDefaults = false,
        $withPrimaryKey = false
    ) {
        $table = Helpers::getTableFromThisModel($this->modelClass);
        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $queryResult = DB::select(/** @lang SQLite */ "PRAGMA table_info($table)");

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->reject(function ($column) use ($withNullables, $withDefaults, $withPrimaryKey) {
                return $column['pk'] && ! $withPrimaryKey
                    || $column['dflt_value'] != null && ! $withDefaults
                    || ! $column['notnull'] && ! $withNullables;
            })
            ->reject(function ($column) use ($modelDefaultAttributes, $withDefaults) {
                return in_array($column['name'], $modelDefaultAttributes) && ! $withDefaults;
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function requiredFieldsForMysqlAndMariaDb()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);
        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $queryResult = DB::select(
            /** @lang SQLite */ "
            SELECT
                COLUMN_NAME AS name,
                COLUMN_TYPE AS type,
                IF(IS_NULLABLE = 'YES', 1, 0) AS nullable,
                COLUMN_DEFAULT AS `default`,
                IF(COLUMN_KEY = 'PRI', 1, 0) AS `primary`
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->map(function ($column) { // specific to mariadb
                if ($column['default'] == 'NULL') {
                    $column['default'] = null;
                }

                return $column;
            })
            ->reject(function ($column) {
                return $column['primary']
                    || $column['default'] != null
                    || $column['nullable'];
            })
            ->reject(function ($column) use ($modelDefaultAttributes) {
                return in_array($column['name'], $modelDefaultAttributes);
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function primaryFieldForMysqlAndMariaDb()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $queryResult = DB::select(
        /** @lang SQLite */ "
            SELECT
                COLUMN_NAME AS name,
                COLUMN_TYPE AS type,
                IF(IS_NULLABLE = 'YES', 1, 0) AS nullable,
                COLUMN_DEFAULT AS `default`,
                IF(COLUMN_KEY = 'PRI', 1, 0) AS `primary`
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->map(function ($column) { // specific to mariadb
                if ($column['default'] == 'NULL') {
                    $column['default'] = null;
                }

                return $column;
            })
            ->filter(function ($column) {
                return $column['primary'];
            })
            ->pluck('name')
            ->toArray();
    }


    /**
     * @return string[]
     */
    protected function allFieldsForMysqlAndMariaDb()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        // todo simplify the query
        $queryResult = DB::select(
        /** @lang SQLite */ "
            SELECT
                COLUMN_NAME AS name,
                COLUMN_TYPE AS type,
                IF(IS_NULLABLE = 'YES', 1, 0) AS nullable,
                COLUMN_DEFAULT AS `default`,
                IF(COLUMN_KEY = 'PRI', 1, 0) AS `primary`
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->map(function ($column) { // specific to mariadb
                if ($column['default'] == 'NULL') {
                    $column['default'] = null;
                }

                return $column;
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function nullableFieldsForMysqlAndMariaDb()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        // todo exclude not nullable from the query
        $queryResult = DB::select(
            /** @lang SQLite */ "
            SELECT
                COLUMN_NAME AS name,
                COLUMN_TYPE AS type,
                IF(IS_NULLABLE = 'YES', 1, 0) AS nullable,
                COLUMN_DEFAULT AS `default`,
                IF(COLUMN_KEY = 'PRI', 1, 0) AS `primary`
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->map(function ($column) { // specific to mariadb
                if ($column['default'] == 'NULL') {
                    $column['default'] = null;
                }

                return $column;
            })
            ->filter(function ($column) {
                return $column['nullable'];
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function getRequiredFieldsForMysqlAndMariaDb(
        $withNullables = false,
        $withDefaults = false,
        $withPrimaryKey = false
    ) {
        $table = Helpers::getTableFromThisModel($this->modelClass);
        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $queryResult = DB::select(
            /** @lang SQLite */ "
            SELECT
                COLUMN_NAME AS name,
                COLUMN_TYPE AS type,
                IF(IS_NULLABLE = 'YES', 1, 0) AS nullable,
                COLUMN_DEFAULT AS `default`,
                IF(COLUMN_KEY = 'PRI', 1, 0) AS `primary`
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->map(function ($column) { // specific to mariadb
                if ($column['default'] == 'NULL') {
                    $column['default'] = null;
                }

                return $column;
            })
            ->reject(function ($column) use ($withNullables, $withDefaults, $withPrimaryKey) {
                return $column['primary'] && ! $withPrimaryKey
                    || $column['default'] != null && ! $withDefaults
                    || $column['nullable'] && ! $withNullables;
            })
            ->reject(function ($column) use ($modelDefaultAttributes, $withDefaults) {
                return in_array($column['name'], $modelDefaultAttributes) && ! $withDefaults;
            })
            ->pluck('name')
            ->toArray();
    }



    /**
     * @return string[]
     */
    protected function primaryFieldForPostgres()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $primaryIndex = DB::select(/** @lang PostgreSQL */ "
            SELECT
                ic.relname AS name,
                string_agg(a.attname, ',' ORDER BY indseq.ord) AS columns,
                am.amname AS type,
                i.indisunique AS unique,
                i.indisprimary AS primary
            FROM
                pg_index i
                JOIN pg_class tc ON tc.oid = i.indrelid
                JOIN pg_namespace tn ON tn.oid = tc.relnamespace
                JOIN pg_class ic ON ic.oid = i.indexrelid
                JOIN pg_am am ON am.oid = ic.relam
                JOIN LATERAL unnest(i.indkey) WITH ORDINALITY AS indseq(num, ord) ON true
                LEFT JOIN pg_attribute a ON a.attrelid = i.indrelid
                AND a.attnum = indseq.num
            WHERE
                tc.relname = ?
                AND tn.nspname = CURRENT_SCHEMA
            GROUP BY
                ic.relname,
                am.amname,
                i.indisunique,
                i.indisprimary;
        ", [$table]);

        return collect($primaryIndex)
            ->map(function ($index) {
                return (array) $index;
            })
            ->filter(function ($index) {
                return $index['primary'];
            })
            ->pluck('columns')
            ->flatten()
            ->toArray();
    }


    /**
     * @return string[]
     */
    protected function allFieldsForPostgres()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $queryResult = DB::select(
            /** @lang PostgreSQL */ '
            SELECT
                is_nullable AS nullable,
                column_name AS name,
                column_default AS default
            FROM
                information_schema.columns
            WHERE
                table_name = ?
            ORDER BY
                ordinal_position ASC',
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->pluck('name')
            ->unique()
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function nullableFieldsForPostgres()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        // todo just take nullables from the query
        $queryResult = DB::select(
            /** @lang PostgreSQL */ '
            SELECT
                is_nullable AS nullable,
                column_name AS name,
                column_default AS default
            FROM
                information_schema.columns
            WHERE
                table_name = ?
            ORDER BY
                ordinal_position ASC',
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->filter(function ($column) {
                return ($column['nullable'] == 'YES');
            })
            ->pluck('name')
            ->unique()
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function getRequiredFieldsForPostgres(
        $withNullables = false,
        $withDefaults = false,
        $withPrimaryKey = false
    ) {
        $table = Helpers::getTableFromThisModel($this->modelClass);
        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $primaryIndex = DB::select(/** @lang PostgreSQL */ "
            SELECT
                ic.relname AS name,
                string_agg(a.attname, ',' ORDER BY indseq.ord) AS columns,
                am.amname AS type,
                i.indisunique AS unique,
                i.indisprimary AS primary
            FROM
                pg_index i
                JOIN pg_class tc ON tc.oid = i.indrelid
                JOIN pg_namespace tn ON tn.oid = tc.relnamespace
                JOIN pg_class ic ON ic.oid = i.indexrelid
                JOIN pg_am am ON am.oid = ic.relam
                JOIN LATERAL unnest(i.indkey) WITH ORDINALITY AS indseq(num, ord) ON true
                LEFT JOIN pg_attribute a ON a.attrelid = i.indrelid
                AND a.attnum = indseq.num
            WHERE
                tc.relname = ?
                AND tn.nspname = CURRENT_SCHEMA
            GROUP BY
                ic.relname,
                am.amname,
                i.indisunique,
                i.indisprimary;
        ", [$table]);

        $primaryIndex = collect($primaryIndex)
            ->map(function ($index) {
                return (array) $index;
            })
            ->filter(function ($index) {
                return $index['primary'];
            })
            ->pluck('columns')
            ->flatten()
            ->toArray();

        $queryResult = DB::select(
            /** @lang PostgreSQL */ '
            SELECT
                is_nullable AS nullable,
                column_name AS name,
                column_default AS default
            FROM
                information_schema.columns
            WHERE
                table_name = ?
            ORDER BY
                ordinal_position ASC',
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->reject(function ($column) use ($primaryIndex, $withDefaults, $withNullables) {
                return ($column['default'] && ! $withDefaults) ||
                    ($column['nullable'] == 'YES' && ! $withNullables) ||
                    (in_array($column['name'], $primaryIndex));
            })
            ->reject(function ($column) use ($modelDefaultAttributes, $withDefaults) {
                return in_array($column['name'], $modelDefaultAttributes) && ! $withDefaults;
            })
            ->pluck('name')
            ->when($withPrimaryKey, function ($collection) use ($primaryIndex) {
                return $collection->prepend(...$primaryIndex);
            })
            ->unique()
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function requiredFieldsForPostgres() {
        $table = Helpers::getTableFromThisModel($this->modelClass);
        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $primaryIndex = DB::select(/** @lang PostgreSQL */ "
            SELECT
                ic.relname AS name,
                string_agg(a.attname, ',' ORDER BY indseq.ord) AS columns,
                am.amname AS type,
                i.indisunique AS unique,
                i.indisprimary AS primary
            FROM
                pg_index i
                JOIN pg_class tc ON tc.oid = i.indrelid
                JOIN pg_namespace tn ON tn.oid = tc.relnamespace
                JOIN pg_class ic ON ic.oid = i.indexrelid
                JOIN pg_am am ON am.oid = ic.relam
                JOIN LATERAL unnest(i.indkey) WITH ORDINALITY AS indseq(num, ord) ON true
                LEFT JOIN pg_attribute a ON a.attrelid = i.indrelid
                AND a.attnum = indseq.num
            WHERE
                tc.relname = ?
                AND tn.nspname = CURRENT_SCHEMA
            GROUP BY
                ic.relname,
                am.amname,
                i.indisunique,
                i.indisprimary;
        ", [$table]);

        $primaryIndex = collect($primaryIndex)
            ->map(function ($index) {
                return (array) $index;
            })
            ->filter(function ($index) {
                return $index['primary'];
            })
            ->pluck('columns')
            ->flatten()
            ->toArray();

        $queryResult = DB::select(
        /** @lang PostgreSQL */ '
            SELECT
                is_nullable AS nullable,
                column_name AS name,
                column_default AS default
            FROM
                information_schema.columns
            WHERE
                table_name = ?
            ORDER BY
                ordinal_position ASC',
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->reject(function ($column) use ($primaryIndex) {
                return ($column['default']) ||
                    ($column['nullable'] == 'YES') ||
                    (in_array($column['name'], $primaryIndex));
            })
            ->reject(function ($column) use ($modelDefaultAttributes) {
                return in_array($column['name'], $modelDefaultAttributes);
            })
            ->pluck('name')
            ->unique()
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function requiredFieldsForSqlServer()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);
        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $primaryIndex = DB::select(/** @lang TSQL */ '
            SELECT
                COL_NAME(ic.object_id, ic.column_id) AS [column]
            FROM
                sys.indexes AS i
                INNER JOIN sys.index_columns AS ic
                    ON i.object_id = ic.object_id
                    AND i.index_id = ic.index_id
                INNER JOIN sys.objects AS o
                    ON i.object_id = o.object_id
            WHERE
                i.is_primary_key = 1
                AND o.name = ?
                AND SCHEMA_NAME(o.schema_id) = schema_name()', [$table]);

        $primaryIndex = collect($primaryIndex)
            ->pluck('column')
            ->toArray();

        $queryResult = DB::select(
            /** @lang TSQL */ "
            SELECT
                COLUMN_NAME AS name,
                DATA_TYPE AS type,
                CASE WHEN IS_NULLABLE = 'YES' THEN 1 ELSE 0 END AS nullable,
                COLUMN_DEFAULT AS [default]
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = SCHEMA_NAME()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->reject(function ($column) use ($primaryIndex) {
                return
                    $column['default'] != null
                    || $column['nullable']
                    || (in_array($column['name'], $primaryIndex));
            })
            ->reject(function ($column) use ($modelDefaultAttributes) {
                return in_array($column['name'], $modelDefaultAttributes);
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function primaryFieldForSqlServer()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $primaryIndex = DB::select(/** @lang TSQL */ '
            SELECT
                COL_NAME(ic.object_id, ic.column_id) AS [column]
            FROM
                sys.indexes AS i
                INNER JOIN sys.index_columns AS ic
                    ON i.object_id = ic.object_id
                    AND i.index_id = ic.index_id
                INNER JOIN sys.objects AS o
                    ON i.object_id = o.object_id
            WHERE
                i.is_primary_key = 1
                AND o.name = ?
                AND SCHEMA_NAME(o.schema_id) = schema_name()', [$table]);

        return collect($primaryIndex)
            ->pluck('column')
            ->toArray();
    }


    /**
     * @return string[]
     */
    protected function allFieldsForSqlServer()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $queryResult = DB::select(
        /** @lang TSQL */ "
            SELECT
                COLUMN_NAME AS name,
                DATA_TYPE AS type,
                CASE WHEN IS_NULLABLE = 'YES' THEN 1 ELSE 0 END AS nullable,
                COLUMN_DEFAULT AS [default]
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = SCHEMA_NAME()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->pluck('name')
            ->toArray();
    }


    /**
     * @return string[]
     */
    protected function nullableFieldsForSqlServer()
    {
        $table = Helpers::getTableFromThisModel($this->modelClass);

        $queryResult = DB::select(
            /** @lang TSQL */ "
            SELECT
                COLUMN_NAME AS name,
                DATA_TYPE AS type,
                CASE WHEN IS_NULLABLE = 'YES' THEN 1 ELSE 0 END AS nullable,
                COLUMN_DEFAULT AS [default]
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = SCHEMA_NAME()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->filter(function ($column) {
                return $column['nullable'];
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function getRequiredFieldsForSqlServer(
        $withNullables = false,
        $withDefaults = false,
        $withPrimaryKey = false
    ) {
        $table = Helpers::getTableFromThisModel($this->modelClass);
        $modelDefaultAttributes = Helpers::getModelDefaultAttributes($this->modelClass);

        $primaryIndex = DB::select(/** @lang TSQL */ '
            SELECT
                COL_NAME(ic.object_id, ic.column_id) AS [column]
            FROM
                sys.indexes AS i
                INNER JOIN sys.index_columns AS ic
                    ON i.object_id = ic.object_id
                    AND i.index_id = ic.index_id
                INNER JOIN sys.objects AS o
                    ON i.object_id = o.object_id
            WHERE
                i.is_primary_key = 1
                AND o.name = ?
                AND SCHEMA_NAME(o.schema_id) = schema_name()', [$table]);

        $primaryIndex = collect($primaryIndex)
            ->pluck('column')
            ->toArray();

        $queryResult = DB::select(
            /** @lang TSQL */ "
            SELECT
                COLUMN_NAME AS name,
                DATA_TYPE AS type,
                CASE WHEN IS_NULLABLE = 'YES' THEN 1 ELSE 0 END AS nullable,
                COLUMN_DEFAULT AS [default]
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = SCHEMA_NAME()
                AND TABLE_NAME = ?
            ORDER BY
                ORDINAL_POSITION ASC",
            [$table]
        );

        return collect($queryResult)
            ->map(function ($column) {
                return (array) $column;
            })
            ->reject(function ($column) use ($withDefaults, $withNullables, $primaryIndex, $withPrimaryKey) {
                return
                    $column['default'] != null && ! $withDefaults
                    || $column['nullable'] && ! $withNullables
                    || (in_array($column['name'], $primaryIndex) && ! $withPrimaryKey);
            })
            ->reject(function ($column) use ($modelDefaultAttributes, $withDefaults) {
                return in_array($column['name'], $modelDefaultAttributes) && ! $withDefaults;
            })
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return void
     */
    protected function throwIfNotUsingModelMethodFirst()
    {
        if (is_null($this->modelClass)) {
            throw new InvalidModelClassException('You should use the model method first');
        }
    }

    /**
     * @return mixed
     */
    private function getTableFromModel()
    {
        //todo return Helpers::getTableFromThisModel($this->modelClass);
        return (new $this->modelClass)->getTable();
    }
}
