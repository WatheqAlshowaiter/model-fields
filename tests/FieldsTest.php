<?php

namespace WatheqAlshowaiter\ModelRequiredFields\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use WatheqAlshowaiter\ModelRequiredFields\Exceptions\InvalidModelClassException;
use WatheqAlshowaiter\ModelRequiredFields\Fields;
use WatheqAlshowaiter\ModelRequiredFields\ModelFields;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Brother;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Father;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Grandson;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Mother;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Someone;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Son;

class FieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_fields_for_father_model()
    {
        $this->assertEquals([
            'id',
            'active',
            'name',
            'email',
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ], Fields::model(Father::class)->allFields());
    }

    public function test_get_all_fields_for_mother_model()
    {
        $this->assertEquals([
            'id',
            'types',
            'uuid',
            'ulid',
            'description',
        ], Fields::model(Mother::class)->allFields());
    }

    public function test_get_all_fields_for_son_model()
    {
        $this->assertEquals([
            'id',
            'father_id',
            'mother_id',
        ], Fields::model(Son::class)->allFields());
    }

    public function test_required_fields_for_father_model()
    {
        $this->assertEquals([
            'name',
            'email',
        ], Fields::model(Father::class)->requiredFields());
    }

    public function test_required_fields_for_father_model_for_older_versions()
    {
        $this->assertEquals([
            'name',
            'email',
        ], Fields::model(Father::class)->requiredFieldsForOlderVersions());
    }

    public function test_required_fields_in_order()
    {
        $this->assertNotEquals([
            'email',
            'name',
        ], Fields::model(Father::class)->requiredFields());

        $this->assertNotEquals([
            'email',
            'name',
        ], Fields::model(Father::class)->requiredFieldsForOlderVersions());
    }

    public function test_nullable_fields_for_father_model()
    {
        $expected = [
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->nullableFields());
        $this->assertEquals($expected, Fields::model(Father::class)->nullableFieldsForOlderVersions());
    }

    public function test_nullable_fields_for_mother_model()
    {
        $expected = [
            'description',
        ];
        $this->assertEquals($expected, Fields::model(Mother::class)->nullableFields());
        $this->assertEquals($expected, Fields::model(Mother::class)->nullableFieldsForOlderVersions());
    }

    public function test_nullable_fields_for_son_model()
    {
        $expected = [
            'mother_id',
        ];
        $this->assertEquals($expected, Fields::model(Son::class)->nullableFields());
        $this->assertEquals($expected, Fields::model(Son::class)->nullableFieldsForOlderVersions());
    }

    public function test_nullable_fields_for_brother_model()
    {
        $expected = [
            'number',
        ];
        $this->assertEquals($expected, Fields::model(Brother::class)->nullableFields());
        $this->assertEquals($expected, Fields::model(Brother::class)->nullableFieldsForOlderVersions());
    }

    // todo until here

    public function test_required_fields_with_nullables()
    {
        $expected = [
            'name',
            'email',
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFields($withNullables = true));
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsWithNullables());
    }

    public function test_get_required_fields_with_nullables_for_older_versions()
    {
        $expected = [
            'name',
            'email',
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        $this->assertEquals($expected,
            Fields::model(Father::class)->getRequiredFieldsForOlderVersions($withNullables = true));
    }

    public function test_get_required_fields_with_defaults()
    {
        $expected = [
            'active',
            'name',
            'email',
        ];
        $this->assertEquals($expected,
            Fields::model(Father::class)->getRequiredFields($withNullables = false, $withDefaults = true));
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsForOlderVersions(
            $withNullables = false,
            $withDefaults = true
        ));
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsWithDefaults());
    }

    public function test_get_required_with_primary_key()
    {
        $expected = [
            'id',
            'name',
            'email',
        ];

        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFields(
            $withNullables = false,
            $withDefaults = false,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsForOlderVersions(
            $withNullables = false,
            $withDefaults = false,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsWithPrimaryKey());
    }

    public function test_get_required_with_nullables_and_defaults()
    {
        $expected = [
            'active',
            'name',
            'email',
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFields(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = false
        ));

        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsForOlderVersions(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = false
        ));
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsWithNullablesAndDefaults());
    }

    public function test_get_required_with_nullables_and_primary_key()
    {
        $expected = [
            'id',
            'name',
            'email',
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFields(
            $withNullables = true,
            $withDefaults = false,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected,
            Fields::model(Father::class)->getRequiredFieldsWithNullablesAndPrimaryKey());
    }

    public function test_get_required_with_nullables_and_primary_key_for_older_versions()
    {
        $expected = [
            'id',
            'name',
            'email',
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsForOlderVersions(
            $withNullables = true,
            $withDefaults = false,
            $withPrimaryKey = true
        ));
    }

    public function test_get_required_with_defaults_and_primary_key()
    {
        $expected = [
            'id',
            'active',
            'name',
            'email',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFields(
            $withNullables = false,
            $withDefaults = true,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsForOlderVersions(
            $withNullables = false,
            $withDefaults = true,
            $withPrimaryKey = true
        ));
        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsWithDefaultsAndPrimaryKey());
    }

    public function test_get_required_with_defaults_and_nullables_and_primary_key()
    {
        $expected = [
            'id',
            'active',
            'name',
            'email',
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFields(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Fields::model(Father::class)->getRequiredFieldsForOlderVersions(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = true
        ));
        $this->assertEquals($expected, Fields::model(Father::class)->getAllFields());
    }

    public function test_get_required_fields_for_mother_model()
    {
        $this->assertEquals([
            'uuid',
            'ulid',
        ], Fields::model(Mother::class)->getRequiredFields());
    }

    public function test_get_required_fields_for_mother_model_for_older_versions()
    {
        $this->assertEquals([
            'uuid',
            'ulid',
        ], Fields::model(Mother::class)->getRequiredFieldsForOlderVersions());
    }

    public function test_get_required_fields_for_son_model()
    {
        $this->assertEquals([
            'father_id',
        ], Fields::model(Son::class)->getRequiredFields());
    }

    public function test_get_required_fields_for_son_model_for_older_versions()
    {
        $this->assertEquals([
            'father_id',
        ], Fields::model(Son::class)->getRequiredFieldsForOlderVersions());
    }

    public function test_get_required_fields_excluding_default_model_attributes()
    {
        $this->assertEquals([
            'email',
        ], Fields::model(Brother::class)->getRequiredFields());
    }

    public function test_get_required_fields_with_applications_defaults()
    {
        $expected = [
            'email',
            'name',
        ];

        $this->assertEquals($expected, Fields::model(Brother::class)->getRequiredFieldsWithDefaults());
        $this->assertEquals($expected, Fields::model(Brother::class)->getRequiredFields(
            $withNullables = false, $withDefaults = true
        ));
    }

    public function test_throw_exception_if_model_is_not_extends_of_eloquent_model()
    {
        $this->expectException(InvalidModelClassException::class);
        $this->expectExceptionMessage('Model class must be an instance of Eloquent model');

        Fields::model(Someone::class)->getRequiredFields();
    }

    public function test_accept_models_that_one_of_the_eloquent_ancestors()
    {
        $this->assertEquals([], Fields::model(Grandson::class)->getRequiredFields());
    }

    public function test_throw_exception_if_use_get_methods_before_using_model_method()
    {
        $this->expectException(InvalidModelClassException::class);
        $this->expectExceptionMessage('You should use the model method first');

        ModelFields::getRequiredFields();
    }

    public function test_throw_exception_if_use_get_older_versions_methods_before_using_model_method()
    {
        $this->expectException(InvalidModelClassException::class);
        $this->expectExceptionMessage('You should use the model method first');

        ModelFields::getPrimaryField();
    }
}
