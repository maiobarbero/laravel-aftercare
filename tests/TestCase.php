<?php

declare(strict_types=1);

namespace MaioBarbero\LaravelAftercare\Tests;

use MaioBarbero\LaravelAftercare\AftercareServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AftercareServiceProvider::class,
        ];
    }
}
