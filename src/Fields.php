<?php

namespace WatheqAlshowaiter\ModelRequiredFields;

use Illuminate\Support\Facades\Facade;

class Fields extends Facade
{
    /**
     * @see FieldsService::allFields()
     *
     * @method static string[] allFields()
     *
     *
     * @see FieldsService::model()
     *
     * @method static FieldsService model($modelClass)
     *
     *  @see FieldsService::requiredFields()
     *
     * @method static string[] requiredFields()
     *
     *  @see FieldsService::nullableFields()
     *
     * @method static string[] nullableFields()
     *
     *  @see FieldsService::requiredFieldsForOlderVersions()
     *
     * @method static string[] requiredFieldsForOlderVersions()
     *
     *  @see FieldsService::nullableFieldsForOlderVersions()
     *
     * @method static string[] nullableFieldsForOlderVersions()
     *
     *  @see FieldsService::applicationDefaultFields()
     *
     * @method static string[] applicationDefaultFields()
     *
     *  @see FieldsService::defaultFields()
     *
     * @method static string[] defaultFields()
     */
    protected static function getFacadeAccessor(): string
    {
        return FieldsService::class;
    }
}
