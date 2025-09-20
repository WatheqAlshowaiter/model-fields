<?php

namespace WatheqAlshowaiter\ModelFields\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use RuntimeException;
use WatheqAlshowaiter\ModelFields\Console\ModelFieldsCommand;
use WatheqAlshowaiter\ModelFields\Tests\Models\Father;

class ModelFieldsCommandTest extends TestCase
{
    use RefreshDatabase;

    public const SUCCESS_EXIT_CODE = 0;

    public const FAILURE_EXIT_CODE = 1;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever(ModelFieldsCommand::STAR_PROMPT_CACHE_KEY, true);
    }

    protected function tearDown(): void
    {
        Cache::forget(ModelFieldsCommand::STAR_PROMPT_CACHE_KEY);
        parent::tearDown();
    }

    public function test_error_when_no_model_provided()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "model").');

        $this->artisan('model:fields');
    }

    public function test_fail_when_no_actual_model_provided()
    {
        $this->artisan('model:fields', ['model' => 'NonExistentModel'])
            ->expectsOutput("Model class 'NonExistentModel' not found.")
            ->assertExitCode(self::FAILURE_EXIT_CODE);
    }

    public function test_failed_when_format_option_is_not_valid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "format" argument does not exist.');

        $this->artisan('model:fields', ['model' => Father::class, 'format' => 'graph']);
    }

    public function test_failed_when_format_value_is_not_valid()
    {
        $invalidFormat = 'something';

        $this->artisan('model:fields', [
            'model' => Father::class,
            '--required' => true,
            '--format' => $invalidFormat,
        ])->expectsOutput("Invalid format '$invalidFormat'. Use: list, json, or table.")
            ->assertExitCode(self::FAILURE_EXIT_CODE);
    }

    public function test_use_list_format_when_not_provided()
    {
        Cache::forever(ModelFieldsCommand::STAR_PROMPT_CACHE_KEY, true);

        $this->artisan('model:fields', ['model' => Father::class])
            ->assertExitCode(self::SUCCESS_EXIT_CODE);

    }

    public function test_fail_when_provided_more_than_one_field_type()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '--required' => true,
            '--nullable' => true,
        ])
            ->expectsOutput('Please specify only one field type option.')
            ->assertExitCode(self::FAILURE_EXIT_CODE);
    }

    public function test_default_to_all_fields_when_no_type_specified()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
        ])
            ->expectsOutput('Father all fields:')
            ->expectsOutput('  - id')
            ->expectsOutput('  - active')
            ->expectsOutput('  - name')
            ->expectsOutput('  - email')
            ->expectsOutput('  - username')
            ->expectsOutput('  - created_at')
            ->expectsOutput('  - updated_at')
            ->expectsOutput('  - deleted_at');
    }

    public function test_uses_type_when_specified()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '--required' => true,
        ])
            ->expectsOutput('Father required fields:')
            ->expectsOutput('  - name')
            ->expectsOutput('  - email');
    }

    public function test_uses_type_shortname_when_specified()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '-r' => true,
        ])
            ->expectsOutput('Father required fields:')
            ->expectsOutput('  - name')
            ->expectsOutput('  - email');
    }

    public function test_json_format()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '--nullable' => true,
            '--format' => 'json',
        ])
            ->expectsOutput('[
    "username",
    "created_at",
    "updated_at",
    "deleted_at"
]');
    }

    public function test_table_format()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '--primary' => true,
            '--format' => 'table',
        ])
            ->expectsOutput('+-----------------------+')
            ->expectsOutput('| Father primary fields |')
            ->expectsOutput('+-----------------------+')
            ->expectsOutput('| id                    |')
            ->expectsOutput('+-----------------------+');
    }

    public function test_json_format_with_empty_results()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '-A' => true,
            '--format' => 'json',
        ])->expectsOutput('No app-default fields found for Father model.');
    }

    public function test_table_format_with_empty_results()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '-A' => true,
            '--format' => 'table',
        ])->expectsOutput('No app-default fields found for Father model.');
    }

    public function test_list_format_with_empty_results()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '-A' => true,
        ])->expectsOutput('No app-default fields found for Father model.');
    }

    public function test_does_not_ask_if_already_cached()
    {
        Cache::forever(ModelFieldsCommand::STAR_PROMPT_CACHE_KEY, true); // make it clear here

        $this->artisan('model:fields', [
            'model' => Father::class,
        ])->assertExitCode(0);

        $this->assertTrue(Cache::get(ModelFieldsCommand::STAR_PROMPT_CACHE_KEY));
    }

    public function test_asks_and_user_declines()
    {
        Cache::forget(ModelFieldsCommand::STAR_PROMPT_CACHE_KEY);

        $this->artisan('model:fields', [
            'model' => Father::class,
        ])
            ->expectsQuestion('🌟 Help other developers find this package by starring it on GitHub?', false)
            ->assertExitCode(self::SUCCESS_EXIT_CODE);

        $this->assertTrue(Cache::get(ModelFieldsCommand::STAR_PROMPT_CACHE_KEY));
    }

    public function test_asks_and_user_accepts()
    {
        Cache::forget(ModelFieldsCommand::STAR_PROMPT_CACHE_KEY);

        // Create a subclass that overrides openUrl()
        $stubCommand = new class extends ModelFieldsCommand
        {
            public string $calledWith = '';

            // Change to protected so test can inspect
            protected function openUrl(string $url): void
            {
                // Do NOT actually exec() — just record that it was called
                $this->calledWith = $url;
            }
        };

        // Replace the command in Laravel's container so artisan uses our stub
        $this->app->extend(ModelFieldsCommand::class, fn () => $stubCommand);

        $this->artisan('model:fields', [
            'model' => Father::class,
        ])
            ->expectsQuestion('🌟 Help other developers find this package by starring it on GitHub?', true)
            ->expectsOutput('Thank you!')
            ->assertExitCode(self::SUCCESS_EXIT_CODE);

        $this->assertStringContainsString('github.com/WatheqAlshowaiter/model-fields', $stubCommand->calledWith);
    }
}
