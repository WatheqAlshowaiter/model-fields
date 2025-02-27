<?php

namespace WatheqAlshowaiter\ModelRequiredFields;

use Illuminate\Support\Facades\Facade;

class ModelFields extends Facade
{
    /**
     * @see ModelFieldsService::getRequiredFieldsForOlderVersions()
     * @method static string[] getRequiredFieldsForOlderVersions($withNullables = false, $withDefaults = false, $withPrimaryKey = false)

     * @see ModelFieldsService::getAllFields()
     * @method static string[] getAllFields()

     * @see ModelFieldsService::getRequiredFieldsWithDefaults()
     * @method static string[] getRequiredFieldsWithDefaults()

     * @see ModelFieldsService::getRequiredFieldsWithDefaultsAndPrimaryKey()
     * @method static string[] getRequiredFieldsWithDefaultsAndPrimaryKey()

     * @see ModelFieldsService::getRequiredFieldsWithNullablesAndDefaults()
     * @method static string[] getRequiredFieldsWithNullablesAndDefaults()

     * @see ModelFieldsService::getRequiredFieldsWithNullablesAndPrimaryKey()
     * @method static string[] getRequiredFieldsWithNullablesAndPrimaryKey()

     * @see ModelFieldsService::getRequiredFields()
     * @method static string[] getRequiredFields($withNullables = false, $withDefaults = false, $withPrimaryKey = false)

     * @see ModelFieldsService::getPrimaryField()
     * @method static string[] getPrimaryField()

     * @see ModelFieldsService::getRequiredFieldsWithNullables()
     * @method static string[] getRequiredFieldsWithNullables()

     * @see ModelFieldsService::model()
     * @method static ModelFieldsService model($modelClass)

     * @see ModelFieldsService::getRequiredFieldsWithPrimaryKey()
     * @method static string[] getRequiredFieldsWithPrimaryKey()
     */
    protected static function getFacadeAccessor(): string
    {
        return ModelFieldsService::class;
    }
}
