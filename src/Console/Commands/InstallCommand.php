<?php

declare(strict_types=1);

namespace MaioBarbero\LaravelAftercare\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use MaioBarbero\LaravelAftercare\AftercareServiceProvider;

class InstallCommand extends Command
{
    protected $signature = 'aftercare:install
                            {--f|force : Replace existing tool and application configuration}';

    protected $description = 'Install all default tools and publish configuration and stubs';

    public function handle(Filesystem $files): int
    {
        if (! $this->option('force')) {
            foreach ([
                base_path('pint.json'),
                base_path('phpstan.neon'),
                base_path('phpstan.neon.dist'),
                base_path('phpstan.dist.neon'),
                base_path('rector.php'),
                config_path('aftercare.php'),
            ] as $path) {
                if ($files->exists($path)) {
                    $this->components->error("{$path} already exists. Use --force to replace configuration, or run the individual installers.");

                    return self::FAILURE;
                }
            }
        }

        foreach ([PintInstallCommand::class, PhpStanInstallCommand::class, RectorInstallCommand::class] as $command) {
            if ($this->call($command, ['--force' => $this->option('force')]) !== self::SUCCESS) {
                $this->components->error('Setup stopped. Completed steps remain in place; fix the error and run the remaining installers.');

                return self::FAILURE;
            }
        }

        if ($this->call('vendor:publish', [
            '--provider' => AftercareServiceProvider::class,
            '--tag' => ['aftercare-config'],
            '--force' => $this->option('force'),
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->call('vendor:publish', [
            '--provider' => AftercareServiceProvider::class,
            '--tag' => ['aftercare-stubs'],
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->components->info('Setup complete. Customize config/aftercare.php and the tool configuration files to suit your project.');

        return self::SUCCESS;
    }
}
