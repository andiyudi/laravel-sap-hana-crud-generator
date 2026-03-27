<?php

namespace Custom\LaravelHana;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\DatabaseManager;

class HanaServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->resolving('db', function (DatabaseManager $db) {
            $db->extend('hana', function ($config, $name) {
                $config['name'] = $name;

                $connector  = new HanaConnector();
                $connection = $connector->connect($config);

                return new HanaConnection(
                    $connection,
                    $config['database'] ?? '',
                    $config['prefix'] ?? '',
                    $config
                );
            });
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
