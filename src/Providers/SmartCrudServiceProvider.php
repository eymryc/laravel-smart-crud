<?php

namespace Rouangni\SmartCrud\Providers;

use Illuminate\Support\ServiceProvider;
use Rouangni\SmartCrud\Commands\SmartCrudCommand;
use Rouangni\SmartCrud\Generators\ApiCrudGenerator;
use Rouangni\SmartCrud\Generators\WebCrudGenerator;
use Rouangni\SmartCrud\Generators\CommonGenerator;

class SmartCrudServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/smart-crud.php',
            'smart-crud'
        );

        // Register generators
        $this->app->singleton(ApiCrudGenerator::class);
        $this->app->singleton(WebCrudGenerator::class);
        $this->app->singleton(CommonGenerator::class);

        // Register main command
        $this->app->singleton(SmartCrudCommand::class, function ($app) {
            return new SmartCrudCommand(
                $app->make(ApiCrudGenerator::class),
                $app->make(WebCrudGenerator::class),
                $app->make(CommonGenerator::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/smart-crud.php' => config_path('smart-crud.php'),
        ], 'smart-crud-config');

        // Publish stubs for customization
        $this->publishes([
            __DIR__ . '/../Stubs' => resource_path('stubs/smart-crud'),
        ], 'smart-crud-stubs');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SmartCrudCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            SmartCrudCommand::class,
            ApiCrudGenerator::class,
            WebCrudGenerator::class,
            CommonGenerator::class,
        ];
    }
}