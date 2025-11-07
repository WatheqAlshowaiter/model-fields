<?php

namespace WatheqAlshowaiter\ModelFields\Tests;

use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase as Orchestra;
use WatheqAlshowaiter\ModelFields\ModelFieldsServiceProvider;
use WatheqAlshowaiter\ModelFields\Tests\Listeners\UncleCreatingListener;
use WatheqAlshowaiter\ModelFields\Tests\Listeners\UncleSavingListener;
use WatheqAlshowaiter\ModelFields\Tests\Models\UncleCreating;
use WatheqAlshowaiter\ModelFields\Tests\Models\UncleSaving;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register event listeners for Uncle model events
        Event::listen(UncleCreating::class, UncleCreatingListener::class);
        Event::listen(UncleSaving::class, UncleSavingListener::class);

        // Register listeners for the eloquent events that the package uses for detection
        Event::listen(
            'eloquent.creating: WatheqAlshowaiter\ModelFields\Tests\Models\Uncle',
            function ($model) {
                (new UncleCreatingListener())->handle(new UncleCreating($model));
            }
        );
        Event::listen(
            'eloquent.saving: WatheqAlshowaiter\ModelFields\Tests\Models\Uncle',
            function ($model) {
                (new UncleSavingListener())->handle(new UncleSaving($model));
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ModelFieldsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $dbConnection = env('DB_CONNECTION', 'sqlite');

        $app['config']->set('database.default', $dbConnection);

        if ($dbConnection === 'mysql') {
            $app['config']->set('database.connections.mysql', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'laravel'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
            ]);
        }

        if ($dbConnection === 'mariadb') {
            $app['config']->set('database.connections.mariadb', [
                'driver' => 'mariadb',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('MARIADB_DATABASE', 'laravel'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ]);
        }

        if ($dbConnection === 'pgsql') {
            $app['config']->set('database.connections.pgsql', [
                'driver' => 'pgsql',
                'host' => env('PGSQL_DB_HOST', '127.0.0.1'),
                'port' => env('PGSQL_DB_PORT', '5432'),
                'database' => env('POSTGRES_DB', 'laravel'),
                'username' => env('POSTGRES_USER', 'forge'),
                'password' => env('POSTGRES_PASSWORD', 'password'),
            ]);
        }

        if ($dbConnection === 'sqlite') {
            $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ]);
        }

        if ($dbConnection === 'sqlsrv') {
            $app['config']->set('database.connections.sqlsrv', [
                'driver' => 'sqlsrv',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '1433'),
                'database' => env('DB_DATABASE', 'master'),
                'username' => env('DB_USERNAME', 'SA'),
                'password' => env('DB_PASSWORD', 'Forge123'),
                'encrypt' => env('DB_ENCRYPT', true),
                'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', true),
            ]);
        }

        // if not supported above then stop the test
        if (! in_array($dbConnection, ['mysql', 'mariadb', 'pgsql', 'sqlite', 'sqlsrv'])) {
            echo "database $dbConnection is not supported";
            exit;
        }
    }
}
