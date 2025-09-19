<?php

namespace WatheqAlshowaiter\ModelFields\Console;

use Illuminate\Support\Facades\Cache;

class ThanksCommand
{
    private const CACHE_KEY = 'model-fields.banner_shown';

    public static function show(): void
    {
        if (self::runningInCi() || Cache::get(self::CACHE_KEY)) {
            return;
        }

        // Skip if running in a non-interactive environment
        if (function_exists('posix_isatty') && ! posix_isatty(STDIN)) {
            echo "\n🎉 Thanks for using Model Fields!\n";

            return;
        }

        echo "\n";
        echo "🎉 Thanks for using Model Fields!\n";
        echo "If you find this package useful, please consider starring it on GitHub.\n";
        echo "Repository: https://github.com/WatheqAlshowaiter/model-fields\n";
        echo "\n";
        echo 'Would you like to open the repository in your browser? [y/N]: ';

        $handle = fopen('php://stdin', 'r');
        $response = strtolower(trim(fgets($handle)));
        fclose($handle);

        if ($response === 'y' || $response === 'yes') {
            self::openUrl('https://github.com/WatheqAlshowaiter/model-fields');
            echo "Opening repository in your browser...\n";
        }

        echo "Thank you! 🙏\n\n";

        Cache::forever(self::CACHE_KEY, true);
    }

    protected static function runningInCi(): bool
    {
        return getenv('CI')
            || getenv('GITHUB_ACTIONS')
            || app()->environment() === 'testing';
    }

    protected static function openUrl(string $url): void
    {
        switch (PHP_OS_FAMILY) {
            case 'Darwin':
                $command = 'open';
                break;
            case 'Windows':
                $command = 'start';
                break;
            default:
                $command = 'xdg-open';
                break;
        }

        exec(sprintf('%s %s', $command, escapeshellarg($url)));
    }
}
