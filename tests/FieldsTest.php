<?php

namespace WatheqAlshowaiter\ModelRequiredFields\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use WatheqAlshowaiter\ModelRequiredFields\Exceptions\InvalidModelClassException;
use WatheqAlshowaiter\ModelRequiredFields\Fields;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Brother;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Father;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Grandson;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Mother;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Someone;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Son;

class FieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_throw_exception_if_model_is_not_extends_of_eloquent_model()
    {
        $this->expectException(InvalidModelClassException::class);
        $this->expectExceptionMessage('Model class must be an instance of Eloquent model');

        Fields::model(Someone::class)->requiredFields();
    }

    public function test_accept_models_that_one_of_the_eloquent_ancestors()
    {
        $this->assertEquals([], Fields::model(Grandson::class)->requiredFields());
    }

    public function test_throw_exception_if_use_get_methods_before_using_model_method()
    {
        $this->expectException(InvalidModelClassException::class);
        $this->expectExceptionMessage('You should use the model method first');

        Fields::requiredFields();
    }

    public function test_throw_exception_if_use_get_older_versions_methods_before_using_model_method()
    {
        $this->expectException(InvalidModelClassException::class);
        $this->expectExceptionMessage('You should use the model method first');

        Fields::primaryField();
    }

    public function test_all_fields_for_father_model()
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
        $this->assertEquals($expected, Fields::model(Father::class)->allFields());
        $this->assertEquals($expected, Fields::model(Father::class)->allFieldsForOlderVersions());
    }

    public function test_all_fields_for_mother_model()
    {
        $expected = [
            'id',
            'types',
            'uuid',
            'ulid',
            'description',
        ];
        $this->assertEquals($expected, Fields::model(Mother::class)->allFields());
        $this->assertEquals($expected, Fields::model(Mother::class)->allFieldsForOlderVersions());
    }

    public function test_all_fields_for_son_model()
    {
        $expected = [
            'id',
            'father_id',
            'mother_id',
        ];
        $this->assertEquals($expected, Fields::model(Son::class)->allFields());
        $this->assertEquals($expected, Fields::model(Son::class)->allFieldsForOlderVersions());
    }

    public function test_required_fields_for_father_model()
    {
        $expected = [
            'name',
            'email',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->requiredFields());
        $this->assertEquals($expected, Fields::model(Father::class)->requiredFieldsForOlderVersions());
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

    public function test_primary_field_for_mother_model()
    {
        $this->assertEquals(['id'], Fields::model(Mother::class)->primaryField());
        $this->assertEquals(['id'], Fields::model(Mother::class)->primaryFieldForOlderVersions());
    }

    public function test_primary_field_for_father_model()
    {
        $this->assertEquals(['id'], Fields::model(Father::class)->primaryField());
        $this->assertEquals(['id'], Fields::model(Father::class)->primaryFieldForOlderVersions());
    }

    public function test_primary_field_for_son_model()
    {
        $this->assertEquals(['id'], Fields::model(Son::class)->primaryField());
        $this->assertEquals(['id'], Fields::model(Son::class)->primaryFieldForOlderVersions());
    }

    public function test_database_default_fields_for_mother_model()
    {
        $expected = [
            'types',
        ];
        $this->assertEquals($expected, Fields::model(Mother::class)->databaseDefaultFields());
        $this->assertEquals($expected, Fields::model(Mother::class)->databaseDefaultFieldsForOlderVersions());
    }

    public function test_database_default_fields_for_father_model()
    {
        $expected = [
            'active',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->databaseDefaultFields());
        $this->assertEquals($expected, Fields::model(Father::class)->databaseDefaultFieldsForOlderVersions());
    }

    public function test_database_default_fields_for_son_model()
    {
        $expected = [];
        $this->assertEquals($expected, Fields::model(Son::class)->databaseDefaultFields());
        $this->assertEquals($expected, Fields::model(Son::class)->databaseDefaultFieldsForOlderVersions());
    }

    public function test_application_default_fields_for_mother_model()
    {
        $expected = [];
        $this->assertEquals($expected, Fields::model(Mother::class)->applicationDefaultFields());
    }

    public function test_application_default_fields_for_father_model()
    {
        $expected = [];
        $this->assertEquals($expected, Fields::model(Father::class)->applicationDefaultFields());
    }

    public function test_application_default_fields_for_brother_model()
    {
        $expected = [
            'name',
            'number',
        ];
        $this->assertEquals($expected, Fields::model(Brother::class)->applicationDefaultFields());
    }

    public function test_default_fields_for_mother_model()
    {
        $expected = [
            'types',
        ];
        $this->assertEquals($expected, Fields::model(Mother::class)->defaultFields());
    }

    public function test_default_fields_for_father_model()
    {
        $expected = [
            'active',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->defaultFields());
    }

    public function test_default_fields_for_brother_model()
    {
        $expected = [
            'name',
            'number',
        ];
        $this->assertEquals($expected, Fields::model(Brother::class)->defaultFields());
    }
}
