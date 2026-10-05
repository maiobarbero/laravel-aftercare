<?php

declare(strict_types=1);

namespace MaioBarbero\LaravelAftercare;

use Illuminate\Support\ServiceProvider;
use MaioBarbero\LaravelAftercare\Configuration\DefaultConfigurator;
use MaioBarbero\LaravelAftercare\Console\Commands\ActionMakeCommand;
use MaioBarbero\LaravelAftercare\Console\Commands\InstallCommand;
use MaioBarbero\LaravelAftercare\Console\Commands\PhpStanInstallCommand;
use MaioBarbero\LaravelAftercare\Console\Commands\PintInstallCommand;
use MaioBarbero\LaravelAftercare\Console\Commands\RectorInstallCommand;

class AftercareServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/aftercare.php', 'aftercare');

        $this->app->singleton(LaravelAftercare::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->app->make(DefaultConfigurator::class)->apply();
        });

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/aftercare.php' => config_path('aftercare.php'),
        ], ['aftercare', 'aftercare-config']);

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/aftercare'),
        ], ['aftercare', 'aftercare-stubs']);

        $this->commands([
            ActionMakeCommand::class,
            InstallCommand::class,
            PhpStanInstallCommand::class,
            PintInstallCommand::class,
            RectorInstallCommand::class,
        ]);
    }
}
