<?php

namespace Rouangni\SmartCrud\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Rouangni\SmartCrud\Commands\SmartCrudCommand;
use Rouangni\SmartCrud\SmartCrudGenerator;

class SmartCrudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/smart-crud.php',
            'smart-crud'
        );

        $this->app->singleton(SmartCrudGenerator::class, function ($app) {
            return new SmartCrudGenerator();
        });
    }

    public function boot(): void
    {
        $this->publishConfig();
        $this->publishStubs();
        $this->registerCommands();
    }

    private function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/smart-crud.php' => config_path('smart-crud.php'),
        ], 'smart-crud-config');
    }

    private function publishStubs(): void
    {
        $this->publishes([
            __DIR__ . '/../Stubs' => resource_path('stubs/smart-crud'),
        ], 'smart-crud-stubs');
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SmartCrudCommand::class,
            ]);
        }
    }
}