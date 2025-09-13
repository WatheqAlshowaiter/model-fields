<?php

namespace WatheqAlshowaiter\ModelRequiredFields;

use Illuminate\Support\Facades\Facade;

class Fields extends Facade
{
    /**
     * @see FieldsService::getRequiredFieldsForOlderVersions()
     *
     * @method static string[] getRequiredFieldsForOlderVersions($withNullables = false, $withDefaults = false, $withPrimaryKey = false)
     *
     * @see FieldsService::allFields()
     *
     * @method static string[] allFields()
     *
     * @see FieldsService::getRequiredFieldsWithDefaults()
     *
     * @method static string[] getRequiredFieldsWithDefaults()
     *
     * @see FieldsService::getRequiredFieldsWithDefaultsAndPrimaryKey()
     *
     * @method static string[] getRequiredFieldsWithDefaultsAndPrimaryKey()
     *
     * @see FieldsService::getRequiredFieldsWithNullablesAndDefaults()
     *
     * @method static string[] getRequiredFieldsWithNullablesAndDefaults()
     *
     * @see FieldsService::getRequiredFieldsWithNullablesAndPrimaryKey()
     *
     * @method static string[] getRequiredFieldsWithNullablesAndPrimaryKey()
     *
     * @see FieldsService::getRequiredFields()
     *
     * @method static string[] getRequiredFields($withNullables = false, $withDefaults = false, $withPrimaryKey = false)
     *
     * @see FieldsService::getPrimaryField()
     *
     * @method static string[] getPrimaryField()
     *
     * @see FieldsService::getRequiredFieldsWithNullables()
     *
     * @method static string[] getRequiredFieldsWithNullables()
     *
     * @see FieldsService::model()
     *
     * @method static FieldsService model($modelClass)
     *
     * @see FieldsService::getRequiredFieldsWithPrimaryKey()
     *
     * @method static string[] getRequiredFieldsWithPrimaryKey()
     *
     *  @see FieldsService::requiredFields()
     *  @method static string[] requiredFields()
     *
     *  @see FieldsService::nullableFields()
     *  @method static string[] nullableFields()
     *
     *  @see FieldsService::requiredFieldsForOlderVersions()
     *  @method static string[] requiredFieldsForOlderVersions()
     *
     *  @see FieldsService::nullableFieldsForOlderVersions()
     *  @method static string[] nullableFieldsForOlderVersions()
     */
    protected static function getFacadeAccessor(): string
    {
        return FieldsService::class;
    }
}
