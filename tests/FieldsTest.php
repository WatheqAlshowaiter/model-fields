<?php

namespace WatheqAlshowaiter\ModelFields\Tests;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionException;
use WatheqAlshowaiter\ModelFields\Exceptions\InvalidModelClassException;
use WatheqAlshowaiter\ModelFields\Fields;
use WatheqAlshowaiter\ModelFields\Tests\Models\Brother;
use WatheqAlshowaiter\ModelFields\Tests\Models\Father;
use WatheqAlshowaiter\ModelFields\Tests\Models\Grandson;
use WatheqAlshowaiter\ModelFields\Tests\Models\Mother;
use WatheqAlshowaiter\ModelFields\Tests\Models\Someone;
use WatheqAlshowaiter\ModelFields\Tests\Models\Son;

class FieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws ReflectionException
     */
    public function test_get_required_fields_only_if_config_enabled_macro()
    {
        /**
         * simulate when config model-required-fields.enable_macro => false
         */
        $this->removeMacro(Builder::class, 'requiredFields');

        $this->expectException(BadMethodCallException::class);
        Father::requiredFields();
    }

    public function test_macro_is_overridden_when_same_static_method_name_added()
    {
        Schema::create('test_table', function ($table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });

        $testModelClass = new class extends Model
        {
            protected $table = 'test_table';

            public static function requiredFields()
            {
                return [
                    'some_field',
                ];
            }
        };

        $this->assertEquals(['some_field'], $testModelClass::requiredFields());

        // but other methods works fine
        $this->assertEquals(['created_at', 'updated_at'], $testModelClass::nullableFields());
    }

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

    public function test_facade_accepts_dynamic_string_class_names()
    {
        $modelClasses = [
            "WatheqAlshowaiter\ModelFields\Tests\Models\Father",
            "WatheqAlshowaiter\ModelFields\Tests\Models\Mother",
        ];

        foreach ($modelClasses as $modelClass) {
            $this->assertEquals(['id'], Fields::model($modelClass)->primaryField());
        }
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
        $this->assertEquals($expected, Father::allFields());
        $this->assertEquals($expected, Father::allFieldsForOlderVersions());
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
        $this->assertEquals($expected, Mother::allFields());
        $this->assertEquals($expected, Mother::allFieldsForOlderVersions());

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
        $this->assertEquals($expected, Son::allFields());
        $this->assertEquals($expected, Son::allFieldsForOlderVersions());
    }

    public function test_required_fields_for_father_model()
    {
        $expected = [
            'name',
            'email',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->requiredFields());
        $this->assertEquals($expected, Fields::model(Father::class)->requiredFieldsForOlderVersions());
        $this->assertEquals($expected, Father::requiredFields());
        $this->assertEquals($expected, Father::requiredFieldsForOlderVersions());
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
        ], Father::requiredFields());

        $this->assertNotEquals([
            'email',
            'name',
        ], Fields::model(Father::class)->requiredFieldsForOlderVersions());

        $this->assertNotEquals([
            'email',
            'name',
        ], Father::requiredFieldsForOlderVersions());
    }

    public function test_required_fields_for_mother_model()
    {
        $expected = [
            'uuid',
            'ulid',
        ];
        $this->assertEquals($expected, Fields::model(Mother::class)->requiredFields());
        $this->assertEquals($expected, Fields::model(Mother::class)->requiredFieldsForOlderVersions());
        $this->assertEquals($expected, Mother::requiredFields());
        $this->assertEquals($expected, Mother::requiredFieldsForOlderVersions());
    }

    public function test_required_fields_for_son_model()
    {
        $expected = [
            'father_id',
        ];
        $this->assertEquals($expected, Fields::model(Son::class)->requiredFields());
        $this->assertEquals($expected, Fields::model(Son::class)->requiredFieldsForOlderVersions());
        $this->assertEquals($expected, Son::requiredFields());
        $this->assertEquals($expected, Son::requiredFieldsForOlderVersions());
    }

    public function test_required_fields_for_brother_model()
    {
        $expected = [
            'email',
        ];
        $this->assertEquals($expected, Fields::model(Brother::class)->requiredFields());
        $this->assertEquals($expected, Fields::model(Brother::class)->requiredFieldsForOlderVersions());
        $this->assertEquals($expected, Brother::requiredFields());
        $this->assertEquals($expected, Brother::requiredFieldsForOlderVersions());
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
        $this->assertEquals($expected, Father::nullableFields());
        $this->assertEquals($expected, Father::nullableFieldsForOlderVersions());
    }

    public function test_nullable_fields_for_mother_model()
    {
        $expected = [
            'description',
        ];
        $this->assertEquals($expected, Fields::model(Mother::class)->nullableFields());
        $this->assertEquals($expected, Fields::model(Mother::class)->nullableFieldsForOlderVersions());
        $this->assertEquals($expected, Mother::nullableFields());
        $this->assertEquals($expected, Mother::nullableFieldsForOlderVersions());
    }

    public function test_nullable_fields_for_son_model()
    {
        $expected = [
            'mother_id',
        ];
        $this->assertEquals($expected, Fields::model(Son::class)->nullableFields());
        $this->assertEquals($expected, Fields::model(Son::class)->nullableFieldsForOlderVersions());
        $this->assertEquals($expected, Son::nullableFields());
        $this->assertEquals($expected, Son::nullableFieldsForOlderVersions());
    }

    public function test_nullable_fields_for_brother_model()
    {
        $expected = [
            'number',
        ];
        $this->assertEquals($expected, Fields::model(Brother::class)->nullableFields());
        $this->assertEquals($expected, Fields::model(Brother::class)->nullableFieldsForOlderVersions());
        $this->assertEquals($expected, Brother::nullableFields());
        $this->assertEquals($expected, Brother::nullableFieldsForOlderVersions());
    }

    public function test_primary_field_for_mother_model()
    {
        $this->assertEquals(['id'], Fields::model(Mother::class)->primaryField());
        $this->assertEquals(['id'], Fields::model(Mother::class)->primaryFieldForOlderVersions());
        $this->assertEquals(['id'], Mother::primaryField());
        $this->assertEquals(['id'], Mother::primaryFieldForOlderVersions());
    }

    public function test_primary_field_for_father_model()
    {
        $this->assertEquals(['id'], Fields::model(Father::class)->primaryField());
        $this->assertEquals(['id'], Fields::model(Father::class)->primaryFieldForOlderVersions());
        $this->assertEquals(['id'], Father::primaryField());
        $this->assertEquals(['id'], Father::primaryFieldForOlderVersions());
    }

    public function test_primary_field_for_son_model()
    {
        $this->assertEquals(['id'], Fields::model(Son::class)->primaryField());
        $this->assertEquals(['id'], Fields::model(Son::class)->primaryFieldForOlderVersions());
        $this->assertEquals(['id'], Son::primaryField());
        $this->assertEquals(['id'], Son::primaryFieldForOlderVersions());
    }

    public function test_database_default_fields_for_mother_model()
    {
        $expected = [
            'types',
        ];
        $this->assertEquals($expected, Fields::model(Mother::class)->databaseDefaultFields());
        $this->assertEquals($expected, Fields::model(Mother::class)->databaseDefaultFieldsForOlderVersions());
        $this->assertEquals($expected, Mother::databaseDefaultFields());
        $this->assertEquals($expected, Mother::databaseDefaultFieldsForOlderVersions());
    }

    public function test_database_default_fields_for_father_model()
    {
        $expected = [
            'active',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->databaseDefaultFields());
        $this->assertEquals($expected, Fields::model(Father::class)->databaseDefaultFieldsForOlderVersions());
        $this->assertEquals($expected, Father::databaseDefaultFields());
        $this->assertEquals($expected, Father::databaseDefaultFieldsForOlderVersions());
    }

    public function test_database_default_fields_for_son_model()
    {
        $expected = [];
        $this->assertEquals($expected, Fields::model(Son::class)->databaseDefaultFields());
        $this->assertEquals($expected, Fields::model(Son::class)->databaseDefaultFieldsForOlderVersions());
        $this->assertEquals($expected, Son::databaseDefaultFields());
        $this->assertEquals($expected, Son::databaseDefaultFieldsForOlderVersions());
    }

    public function test_application_default_fields_for_mother_model()
    {
        $expected = [];
        $this->assertEquals($expected, Fields::model(Mother::class)->applicationDefaultFields());
        $this->assertEquals($expected, Mother::applicationDefaultFields());
    }

    public function test_application_default_fields_for_father_model()
    {
        $expected = [];
        $this->assertEquals($expected, Fields::model(Father::class)->applicationDefaultFields());
        $this->assertEquals($expected, Father::applicationDefaultFields());
    }

    public function test_application_default_fields_for_brother_model()
    {
        $expected = [
            'name',
            'number',
        ];
        $this->assertEquals($expected, Fields::model(Brother::class)->applicationDefaultFields());
        $this->assertEquals($expected, Brother::applicationDefaultFields());
    }

    public function test_default_fields_for_mother_model()
    {
        $expected = [
            'types',
        ];
        $this->assertEquals($expected, Fields::model(Mother::class)->defaultFields());
        $this->assertEquals($expected, Mother::defaultFields());

    }

    public function test_default_fields_for_father_model()
    {
        $expected = [
            'active',
        ];
        $this->assertEquals($expected, Fields::model(Father::class)->defaultFields());
        $this->assertEquals($expected, Father::defaultFields());
    }

    public function test_default_fields_for_brother_model()
    {
        $expected = [
            'name',
            'number',
        ];
        $this->assertEquals($expected, Fields::model(Brother::class)->defaultFields());
        $this->assertEquals($expected, Brother::defaultFields());
    }

    /**
     * @throws ReflectionException
     */
    private function removeMacro(string $class, string $macro): void
    {
        if (! method_exists($class, 'hasMacro')) {
            return;
        }

        $reflection = new ReflectionClass($class);
        $property = $reflection->getProperty('macros');
        $property->setAccessible(true);

        $macros = $property->getValue();
        unset($macros[$macro]);
        $property->setValue($macros);
        $property->setAccessible(false);
    }
}
