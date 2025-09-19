<?php

namespace WatheqAlshowaiter\ModelFields\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use WatheqAlshowaiter\ModelFields\Fields;
use WatheqAlshowaiter\ModelFields\Support\Helpers;

class ModelFieldsCommand extends Command
{
    protected $signature = 'model:fields
                           {model : The model class name (e.g., User, App\\Models\\Post)}
                           {--a|all : Get all fields}
                           {--r|required : Get required fields}
                           {--N|nullable : Get nullable fields}
                           {--p|primary : Get primary key fields}
                           {--d|default : Get default fields}
                           {--A|app-default : Get application default fields}
                           {--D|db-default : Get database default fields}
                           {--format=list : Output format (list|json|table)}';

    protected $description = 'Get model fields fast — required, nullable, primary, or default fields.';

    /**
     * @return int
     */
    public function handle()
    {
        $modelName = $this->argument('model');
        $format = $this->option('format');

        if (!in_array($format, ['list', 'json', 'table'])) {
            $this->error("Invalid format '$format'. Use: list, json, or table.");

            return 1;
        }

        $modelClass = $this->resolveModelClass($modelName);

        if (!$modelClass) {
            $this->error("Model class '$modelName' not found.");

            return 1;
        }

        $type = $this->determineType();

        if ($type === -1) { // error of adding more than one field type
            return 1;
        }

        $fields = $this->getFieldsByType($modelClass, $type);

        $this->outputFields($fields, $format, $type, $modelClass);

        $this->askToStarRepository();

        return 0;
    }

    /**
     * @return string|null
     */
    protected function resolveModelClass(string $modelName)
    {
        // Try as-is first
        if (class_exists($modelName) && $this->isEloquentModel($modelName)) {
            return $modelName;
        }

        // Try common paths with proper case
        $modelName = ucfirst($modelName);
        $possibleClasses = [
            "App\\Models\\$modelName",
            "App\\$modelName",
        ];

        foreach ($possibleClasses as $class) {
            if (class_exists($class) && $this->isEloquentModel($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    private function isEloquentModel(string $class)
    {
        return is_subclass_of($class, 'Illuminate\Database\Eloquent\Model');
    }

    /**
     * @return int|string
     */
    protected function determineType()
    {
        $validTypes = ['all', 'required', 'nullable', 'primary', 'default', 'app-default', 'db-default'];
        $selectedTypes = [];

        foreach ($validTypes as $type) {
            if ($this->option($type)) {
                $selectedTypes[] = $type;
            }
        }

        if (count($selectedTypes) > 1) {
            $this->error('Please specify only one field type option.');

            return -1;
        }

        if (count($selectedTypes) === 1) {
            return $selectedTypes[0];
        }

        return 'all'; // default if no flags are set
    }

    /**
     * @param  string  $modelClass
     * @param  string  $type
     *
     * @return array
     */
    protected function getFieldsByType($modelClass, $type)
    {
        switch ($type) {
            case 'required':
                return Fields::model($modelClass)->requiredFields();
            case 'nullable':
                return Fields::model($modelClass)->nullableFields();
            case 'primary':
                return Fields::model($modelClass)->primaryField();
            case 'default':
                return Fields::model($modelClass)->defaultFields();
            case 'app-default':
                return Fields::model($modelClass)->applicationDefaultFields();
            case 'db-default':
                return Fields::model($modelClass)->databaseDefaultFields();
            case 'all':
            default:
                return Fields::model($modelClass)->allFields();
        }
    }

    /**
     * @param  array  $fields
     * @param  string  $format
     * @param  string  $type
     * @param  string  $modelClass
     *
     * @return void
     */
    protected function outputFields($fields, $format, $type, $modelClass)
    {
        $modelName = class_basename($modelClass);

        switch ($format) {
            case 'json':
                if (empty($fields)) {
                    $this->output("No $type fields found for $modelName model.");

                    return;
                }

                /** @noinspection PhpComposerExtensionStubsInspection */
                $this->output(json_encode($fields, JSON_PRETTY_PRINT));
                break;

            case 'table':
                if (empty($fields)) {
                    $this->output("No $type fields found for $modelName model.");

                    return;
                }

                $tableData = array_map(function ($field) {
                    return [$field];
                }, $fields);
                $this->table(["$modelName $type fields"], $tableData);
                break;

            case 'list':
            default:
                if (empty($fields)) {
                    $this->output("No $type fields found for $modelName model.");

                    return;
                }

                $this->output("$modelName $type fields:");
                foreach ($fields as $field) {
                    $this->output("  - $field");
                }
                break;
        }
    }

    /**
     * Output text with version-aware compatibility
     *
     * @param  string  $text
     *
     * @return void
     */
    private function output($text)
    {
        if ($this->needsLegacyOutputCompatibility()) {
            $this->getOutput()->writeln($text);
        } else {
            $this->line("<info>$text</info>");
        }
    }

    /**
     * Ask user to star the GitHub repository for only the first time he uses the package in terminal
     *
     * @return void
     */
    private function askToStarRepository()
    {
        if (!$this->shouldShowInteractivePrompt()) {
            return;
        }

        $cacheKey = 'model-fields.banner_shown';
        $repo = 'https://github.com/WatheqAlshowaiter/model-fields';

        if (Cache::get($cacheKey)) {
            return;
        }

        $wantsToStar = $this->confirm(
            '🌟 Help other developers find this package by starring it on GitHub?'
        );

        if ($wantsToStar) {
            $this->openUrl($repo);
            $this->output('Thank you!');
        }

        Cache::forever($cacheKey, true);
    }

    /**
     * Determine if we should show interactive prompts based on Laravel version and environment
     *
     * @return bool
     */
    private function shouldShowInteractivePrompt()
    {
        if ($this->needsLegacyOutputCompatibility()) {
            return true;
        }

        return $this->input->isInteractive();
    }

    /**
     * Check if we're running in a testing environment
     *
     * @return bool
     */
    private function isTestingEnvironment()
    {
        return app()->environment('testing');
    }

    /**
     * Check if we need legacy output compatibility for Laravel ≤10 in testing
     *
     * @return bool
     */
    private function needsLegacyOutputCompatibility()
    {
        return $this->isTestingEnvironment() && Helpers::isLaravelVersionLessThanOrEqualTo10();
    }

    /**
     * @return void
     */
    protected function openUrl(string $url)
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            $command = 'open';
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $command = 'start';
        } else {
            $command = 'xdg-open';
        }

        exec(sprintf('%s %s', $command, escapeshellarg($url)));
    }
}
