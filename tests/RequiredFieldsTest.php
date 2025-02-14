<?php

namespace WatheqAlshowaiter\ModelRequiredFields\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Brother;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Father;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Mother;
use WatheqAlshowaiter\ModelRequiredFields\Tests\Models\Son;

class RequiredFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_required_fields_for_father_model()
    {
        $this->assertEquals([
            'name',
            'email',
        ], Father::getRequiredFields());
    }

    public function test_get_required_fields_for_father_model_for_older_versions()
    {
        $this->assertEquals([
            'name',
            'email',
        ], Father::getRequiredFieldsForOlderVersions());
    }

    public function test_get_required_fields_in_order()
    {
        $this->assertNotEquals([
            'email',
            'name',
        ], Father::getRequiredFields());

        $this->assertNotEquals([
            'email',
            'name',
        ], Father::getRequiredFieldsForOlderVersions());
    }

    public function test_get_required_fields_with_nullables()
    {
        $expected = [
            'name',
            'email',
            'username',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        $this->assertEquals($expected, Father::getRequiredFields($withNullables = true));
        $this->assertEquals($expected, Father::getRequiredFieldsWithNullables());
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
        $this->assertEquals($expected, Father::getRequiredFieldsForOlderVersions($withNullables = true));
    }

    public function test_get_required_fields_with_defaults()
    {
        $expected = [
            'active',
            'name',
            'email',
        ];
        $this->assertEquals($expected, Father::getRequiredFields($withNullables = false, $withDefaults = true));
        $this->assertEquals($expected, Father::getRequiredFieldsForOlderVersions(
            $withNullables = false,
            $withDefaults = true
        ));
        $this->assertEquals($expected, Father::getRequiredFieldsWithDefaults());
    }

    public function test_get_required_with_primary_key()
    {
        $expected = [
            'id',
            'name',
            'email',
        ];

        $this->assertEquals($expected, Father::getRequiredFields(
            $withNullables = false,
            $withDefaults = false,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Father::getRequiredFieldsForOlderVersions(
            $withNullables = false,
            $withDefaults = false,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Father::getRequiredFieldsWithPrimaryKey());
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
        $this->assertEquals($expected, Father::getRequiredFields(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = false
        ));

        $this->assertEquals($expected, Father::getRequiredFieldsForOlderVersions(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = false
        ));
        $this->assertEquals($expected, Father::getRequiredFieldsWithNullablesAndDefaults());
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
        $this->assertEquals($expected, Father::getRequiredFields(
            $withNullables = true,
            $withDefaults = false,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Father::getRequiredFieldsWithNullablesAndPrimaryKey());
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

        $this->assertEquals($expected, Father::getRequiredFieldsForOlderVersions(
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
        $this->assertEquals($expected, Father::getRequiredFields(
            $withNullables = false,
            $withDefaults = true,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Father::getRequiredFieldsForOlderVersions(
            $withNullables = false,
            $withDefaults = true,
            $withPrimaryKey = true
        ));
        $this->assertEquals($expected, Father::getRequiredFieldsWithDefaultsAndPrimaryKey());
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

        $this->assertEquals($expected, Father::getRequiredFields(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = true
        ));

        $this->assertEquals($expected, Father::getRequiredFieldsForOlderVersions(
            $withNullables = true,
            $withDefaults = true,
            $withPrimaryKey = true
        ));
        $this->assertEquals($expected, Father::getAllFields());
    }

    public function test_get_required_fields_for_mother_model()
    {
        $this->assertEquals([
            'uuid',
            'ulid',
        ], Mother::getRequiredFields());
    }

    public function test_get_required_fields_for_mother_model_for_older_versions()
    {
        $this->assertEquals([
            'uuid',
            'ulid',
        ], Mother::getRequiredFieldsForOlderVersions());
    }

    public function test_get_required_fields_for_son_model()
    {
        $this->assertEquals([
            'father_id',
        ], Son::getRequiredFields());
    }

    public function test_get_required_fields_for_son_model_for_older_versions()
    {
        $this->assertEquals([
            'father_id',
        ], Son::getRequiredFieldsForOlderVersions());
    }

    public function test_get_required_fields_excluding_default_model_attributes()
    {
        $this->assertEquals([
            'email',
        ], Brother::getRequiredFields());
    }

    public function test_get_required_fields_with_applications_defaults()
    {
        $expected = [
            'email',
            'name',
        ];

        $this->assertEquals($expected, Brother::getRequiredFieldsWithDefaults());
        $this->assertEquals($expected, Brother::getRequiredFields($withNullables = false, $withDefaults = true));
    }
}
