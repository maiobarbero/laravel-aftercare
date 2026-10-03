<?php

declare(strict_types=1);

namespace MaioBarberoDefault\MaioBarberoDefault\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class PintInstallCommand extends Command
{
    protected $signature = 'default:pint
                            {--f|force : Replace an existing pint.json file}';

    protected $description = 'Install Laravel Pint and the default formatting configuration';

    public function handle(Composer $composer, Filesystem $files): int
    {
        $path = base_path('pint.json');

        if ($files->exists($path) && ! $this->option('force')) {
            $this->components->error('pint.json already exists. Use --force to replace it.');

            return self::FAILURE;
        }

        if (! $files->isFile(base_path('composer.json'))) {
            $this->components->error('No composer.json found in the application root.');

            return self::FAILURE;
        }

        $stub = $files->get(__DIR__.'/../../../stubs/pint.json.stub');

        $composer->setWorkingPath(base_path());

        if (! $composer->requirePackages(['laravel/pint', '--no-interaction'], true, $this->output)) {
            $this->components->error('Pint installation failed. Configuration was not changed.');

            return self::FAILURE;
        }

        if ($files->put($path, $stub) === false) {
            $this->components->error('Pint was installed, but pint.json could not be written.');

            return self::FAILURE;
        }

        $this->components->info('Pint installed and pint.json created.');

        return self::SUCCESS;
    }
}
