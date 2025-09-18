<?php

namespace WatheqAlshowaiter\ModelFields;

use Illuminate\Support\Facades\Facade;

class Fields extends Facade
{
    /**
     * @see FieldsService::model()
     *
     * @method static FieldsService model($modelClass)
     *
     * @see FieldsService::allFields()
     *
     * @method static string[] allFields()
     *
     * @see FieldsService::requiredFields()
     *
     * @method static string[] requiredFields()
     *
     * @see FieldsService::nullableFields()
     *
     * @method static string[] nullableFields()
     *
     * @see FieldsService::primaryField()
     *
     * @method static string[] primaryField()
     *
     * @see FieldsService::databaseDefaultFields()
     *
     * @method static string[] databaseDefaultFields()
     *
     * @see FieldsService::nullableFieldsForOlderVersions()
     *
     * @method static string[] nullableFieldsForOlderVersions()
     *
     * @see FieldsService::databaseDefaultFieldsForOlderVersions()
     *
     * @method static string[] databaseDefaultFieldsForOlderVersions()
     *
     * @see FieldsService::applicationDefaultFields()
     *
     * @method static string[] applicationDefaultFields()
     *
     * @see FieldsService::defaultFields()
     *
     * @method static string[] defaultFields()
     *
     * @see FieldsService::primaryFieldForOlderVersions()
     *
     * @method static string[] primaryFieldForOlderVersions()
     *
     * @see FieldsService::requiredFieldsForOlderVersions()
     *
     * @method static string[] requiredFieldsForOlderVersions()
     *
     * @see FieldsService::allFieldsForOlderVersions()
     *
     * @method static string[] allFieldsForOlderVersions()
     */
    protected static function getFacadeAccessor(): string
    {
        return FieldsService::class;
    }
}
