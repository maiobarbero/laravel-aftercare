<?php

declare(strict_types=1);

namespace MaioBarberoDefault\MaioBarberoDefault;

use Illuminate\Support\ServiceProvider;
use MaioBarberoDefault\MaioBarberoDefault\Console\Commands\ActionMakeCommand;
use MaioBarberoDefault\MaioBarberoDefault\Console\Commands\MaioBarberoDefaultCommand;
use MaioBarberoDefault\MaioBarberoDefault\Console\Commands\PhpStanInstallCommand;
use MaioBarberoDefault\MaioBarberoDefault\Console\Commands\PintInstallCommand;
use MaioBarberoDefault\MaioBarberoDefault\Console\Commands\RectorInstallCommand;

class MaioBarberoDefaultServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/default.php', 'default');

        $this->app->singleton(MaioBarberoDefault::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/default.php' => config_path('default.php'),
        ], ['default', 'default-config']);

        $this->commands([
            ActionMakeCommand::class,
            MaioBarberoDefaultCommand::class,
            PhpStanInstallCommand::class,
            PintInstallCommand::class,
            RectorInstallCommand::class,
        ]);
    }
}
