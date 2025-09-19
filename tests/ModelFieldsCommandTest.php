<?php

namespace WatheqAlshowaiter\ModelFields\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;
use WatheqAlshowaiter\ModelFields\Console\ModelFieldsCommand;
use WatheqAlshowaiter\ModelFields\Tests\Models\Father;

class ModelFieldsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function runCommandAndGetOutput(array $params): string
    {
        $buffer = new BufferedOutput();
        Artisan::call('model:fields', $params, $buffer);
        return trim($buffer->fetch());
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
            ->assertFailed();
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
            ->assertFailed();
    }

    public function test_use_list_format_when_not_provided()
    {
        Cache::forever('model-fields.banner_shown', true);

        $this->artisan('model:fields', ['model' => Father::class])
            ->assertSuccessful();

        Cache::forget('model-fields.banner_shown');
    }

    public function test_fail_when_provided_more_than_one_field_type()
    {
        $this->artisan('model:fields', [
            'model' => Father::class,
            '--required' => true,
            '--nullable' => true,
        ])
            ->expectsOutput('Please specify only one field type option.')
            ->assertFailed();
    }

    public function test_default_to_all_fields_when_no_type_specified()
    {
        Cache::forever('model-fields.banner_shown', true);

        $output = $this->runCommandAndGetOutput([
            'model' => Father::class,
        ]);

        $this->assertStringContainsString('Father all fields:', $output);
        $this->assertStringContainsString('  - id', $output);
        $this->assertStringContainsString('  - active', $output);
        $this->assertStringContainsString('  - name', $output);
        $this->assertStringContainsString('  - email', $output);
        $this->assertStringContainsString('  - username', $output);
        $this->assertStringContainsString('  - created_at', $output);
        $this->assertStringContainsString('  - updated_at', $output);
        $this->assertStringContainsString('  - deleted_at', $output);

        Cache::forget('model-fields.banner_shown');
    }

    public function test_uses_type_when_specified()
    {
        Cache::forever('model-fields.banner_shown', true);

        $output = $this->runCommandAndGetOutput([
            'model' => Father::class,
            '--required' => true,
        ]);

        $this->assertStringContainsString('Father required fields:', $output);
        $this->assertStringContainsString('  - name', $output);
        $this->assertStringContainsString('  - email', $output);

        Cache::forget('model-fields.banner_shown');
    }

    public function test_uses_type_shortname_when_specified()
    {
        Cache::forever('model-fields.banner_shown', true);

        $output = $this->runCommandAndGetOutput([
            'model' => Father::class,
            '-r' => true,
        ]);

        $this->assertStringContainsString('Father required fields:', $output);
        $this->assertStringContainsString('  - name', $output);
        $this->assertStringContainsString('  - email', $output);

        Cache::forget('model-fields.banner_shown');
    }

    public function test_json_format()
    {
        Cache::forever('model-fields.banner_shown', true);

        $output = $this->runCommandAndGetOutput([
            'model' => Father::class,
            '--nullable' => true,
            '--format' => 'json',
        ]);

        $this->assertJson($output);
        /** @noinspection PhpComposerExtensionStubsInspection */
        $decoded = json_decode(trim($output), true);
        $this->assertIsArray($decoded);
        $this->assertContains('username', $decoded);
        $this->assertContains('created_at', $decoded);
        $this->assertContains('updated_at', $decoded);
        $this->assertContains('deleted_at', $decoded);
        Cache::forget('model-fields.banner_shown');
    }

    public function test_table_format()
    {
        Cache::forever('model-fields.banner_shown', true);

        $output = $this->runCommandAndGetOutput([
            'model' => Father::class,
            '--primary' => true,
            '--format' => 'table',
        ]);

        $this->assertStringContainsString('Father primary fields', $output);
        $this->assertStringContainsString('id', $output);
        $this->assertStringContainsString('+', $output); // Table border characters
        $this->assertStringContainsString('|', $output); // Table border characters

        Cache::forget('model-fields.banner_shown');
    }

    public function test_json_format_with_empty_results()
    {
        Cache::forever('model-fields.banner_shown', true);

        $output = $this->runCommandAndGetOutput([
            'model' => Father::class,
            '-A' => true,
            '--format' => 'json',
        ]);

        $this->assertStringContainsString('No app-default fields found for Father model.', $output);

        Cache::forget('model-fields.banner_shown');
    }

    public function test_table_format_with_empty_results()
    {
        Cache::forever('model-fields.banner_shown', true);

        $output = $this->runCommandAndGetOutput([
            'model' => Father::class,
            '-A' => true,
            '--format' => 'table',
        ]);

        $this->assertStringContainsString('No app-default fields found for Father model.', $output);

        Cache::forget('model-fields.banner_shown');
    }

    public function test_list_format_with_empty_results()
    {
        Cache::forever('model-fields.banner_shown', true);

        $output = $this->runCommandAndGetOutput([
            'model' => Father::class,
            '-A' => true,
        ]);

        $this->assertStringContainsString('No app-default fields found for Father model.', $output);

        Cache::forget('model-fields.banner_shown');
    }

    public function test_does_not_ask_if_already_cached()
    {
        Cache::forever('model-fields.banner_shown', true);

        $this->artisan('model:fields', [
            'model' => Father::class,
        ])->assertExitCode(0);

        $this->assertTrue(Cache::get('model-fields.banner_shown'));

        Cache::forget('model-fields.banner_shown');
    }

    public function test_asks_and_user_declines()
    {
        Cache::forget('model-fields.banner_shown');

        $this->artisan('model:fields', [
            'model' => Father::class,
        ])
            ->expectsQuestion('🌟 Help other developers find this package by starring it on GitHub?', false)
            ->doesntExpectOutput('Thank you!')
            ->assertExitCode(0);

        $this->assertTrue(Cache::get('model-fields.banner_shown'));
        Cache::forget('model-fields.banner_shown');
    }

    public function test_asks_and_user_accepts()
    {
        Cache::forget('model-fields.banner_shown');

        // Create a subclass that overrides openUrl()
        $stubCommand = new class extends ModelFieldsCommand {
            public string $calledWith = '';

            // Change to protected so test can inspect
            protected function openUrl(string $url): void
            {
                // Do NOT actually exec() — just record that it was called
                $this->calledWith = $url;
            }
        };

        // Replace the command in Laravel's container so artisan uses our stub
        $this->app->extend(ModelFieldsCommand::class, fn() => $stubCommand);

        $this->artisan('model:fields', [
            'model' => Father::class,
        ])
            ->expectsQuestion('🌟 Help other developers find this package by starring it on GitHub?', true)
            ->expectsOutput('Thank you!')
            ->assertExitCode(0);

        $this->assertStringContainsString('github.com/WatheqAlshowaiter/model-fields', $stubCommand->calledWith);

        Cache::forget('model-fields.banner_shown');
    }
}
