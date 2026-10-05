<?php

declare(strict_types=1);

namespace MaioBarbero\LaravelAftercare\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class RectorInstallCommand extends Command
{
    protected $signature = 'aftercare:rector
                            {--f|force : Replace an existing rector.php file}';

    protected $description = 'Install Rector and the default refactoring configuration';

    public function handle(Composer $composer, Filesystem $files): int
    {
        $path = base_path('rector.php');

        if ($files->exists($path) && ! $this->option('force')) {
            $this->components->error('rector.php already exists. Use --force to replace it.');

            return self::FAILURE;
        }

        if (! $files->isFile(base_path('composer.json'))) {
            $this->components->error('No composer.json found in the application root.');

            return self::FAILURE;
        }

        $custom = base_path('stubs/aftercare/rector.php.stub');
        $stub = $files->get($files->isFile($custom) ? $custom : __DIR__.'/../../../stubs/rector.php.stub');

        $composer->setWorkingPath(base_path());

        if (! $composer->requirePackages(['rector/rector', '--no-interaction'], true, $this->output)) {
            $this->components->error('Rector installation failed. Configuration was not changed.');

            return self::FAILURE;
        }

        if ($files->put($path, $stub) === false) {
            $this->components->error('Rector was installed, but rector.php could not be written.');

            return self::FAILURE;
        }

        $this->components->info('Rector installed and rector.php created.');

        return self::SUCCESS;
    }
}
