<?php

declare(strict_types=1);

namespace MaioBarberoDefault\MaioBarberoDefault\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class PhpStanInstallCommand extends Command
{
    protected $signature = 'default:phpstan
                            {--f|force : Write phpstan.neon even if PHPStan configuration already exists}';

    protected $description = 'Install PHPStan with Larastan and the default analysis configuration';

    public function handle(Composer $composer, Filesystem $files): int
    {
        $path = base_path('phpstan.neon');

        if (! $this->option('force')) {
            foreach (['phpstan.neon', 'phpstan.neon.dist', 'phpstan.dist.neon'] as $file) {
                if ($files->exists(base_path($file))) {
                    $action = $file === 'phpstan.neon' ? 'replace it' : 'create phpstan.neon';
                    $this->components->error("{$file} already exists. Use --force to {$action}.");

                    return self::FAILURE;
                }
            }
        }

        if (! $files->isFile(base_path('composer.json'))) {
            $this->components->error('No composer.json found in the application root.');

            return self::FAILURE;
        }

        $stub = $files->get(__DIR__.'/../../../stubs/phpstan.neon.stub');

        $composer->setWorkingPath(base_path());

        if (! $composer->requirePackages(['phpstan/phpstan', 'larastan/larastan', '--no-interaction'], true, $this->output)) {
            $this->components->error('PHPStan installation failed. Configuration was not changed.');

            return self::FAILURE;
        }

        if ($files->put($path, $stub) === false) {
            $this->components->error('PHPStan was installed, but phpstan.neon could not be written.');

            return self::FAILURE;
        }

        $this->components->info('PHPStan installed and phpstan.neon created.');

        return self::SUCCESS;
    }
}
