<?php

declare(strict_types=1);

namespace MaioBarbero\LaravelAftercare\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ActionMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:action
                            {name : The name of the action}
                            {--t|transaction : Wrap the action body in a database transaction}
                            {--f|force : Create the class even if the action already exists}';

    protected $description = 'Create a new action class';

    protected $type = 'Action';

    protected function getStub(): string
    {
        $stub = $this->option('transaction') ? 'action.transaction.stub' : 'action.stub';
        $custom = base_path('stubs/aftercare/'.$stub);

        return $this->files->isFile($custom) ? $custom : __DIR__.'/../../../stubs/'.$stub;
    }

    /**
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Actions';
    }
}
