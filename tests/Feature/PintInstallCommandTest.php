<?php

declare(strict_types=1);

use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\OutputInterface;

beforeEach(function () {
    $path = sys_get_temp_dir().'/default-pint-'.bin2hex(random_bytes(8));
    File::ensureDirectoryExists($path);
    $this->app->setBasePath($path);
    File::put(base_path('composer.json'), '{"require": {}}');
});

afterEach(function () {
    File::deleteDirectory(base_path());
});

it('installs Pint as a development dependency and copies the configuration stub', function () {
    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->once()->with(base_path())->andReturnSelf();
    $composer->shouldReceive('requirePackages')->once()
        ->with(['laravel/pint', '--no-interaction'], true, Mockery::type(OutputInterface::class))
        ->andReturnUsing(function (): bool {
            expect(File::exists(base_path('pint.json')))->toBeFalse();

            return true;
        });

    $this->artisan('default:pint')
        ->expectsOutputToContain('Pint installed and pint.json created.')
        ->assertSuccessful();

    expect(File::get(base_path('pint.json')))
        ->toBe(File::get(__DIR__.'/../../stubs/pint.json.stub'));
});

it('preserves an existing configuration without running Composer', function () {
    File::put(base_path('pint.json'), '{"preset": "psr12"}');
    $this->mock(Composer::class)->shouldNotReceive('requirePackages');

    $this->artisan('default:pint')
        ->expectsOutputToContain('pint.json already exists. Use --force to replace it.')
        ->assertFailed();

    expect(File::get(base_path('pint.json')))->toBe('{"preset": "psr12"}');
});

it('replaces an existing configuration when forced', function (string $option) {
    File::put(base_path('pint.json'), '{"preset": "psr12"}');
    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->once()->with(base_path())->andReturnSelf();
    $composer->shouldReceive('requirePackages')->once()
        ->with(['laravel/pint', '--no-interaction'], true, Mockery::type(OutputInterface::class))
        ->andReturnTrue();

    $this->artisan('default:pint', [$option => true])->assertSuccessful();

    expect(File::get(base_path('pint.json')))
        ->toBe(File::get(__DIR__.'/../../stubs/pint.json.stub'));
})->with(['--force', '-f']);

it('does not write configuration when Composer fails', function (bool $existingConfiguration) {
    if ($existingConfiguration) {
        File::put(base_path('pint.json'), '{"preset": "psr12"}');
    }

    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->once()->with(base_path())->andReturnSelf();
    $composer->shouldReceive('requirePackages')->once()
        ->with(['laravel/pint', '--no-interaction'], true, Mockery::type(OutputInterface::class))
        ->andReturnFalse();

    $this->artisan('default:pint', ['--force' => true])
        ->expectsOutputToContain('Pint installation failed. Configuration was not changed.')
        ->assertFailed();

    if ($existingConfiguration) {
        expect(File::get(base_path('pint.json')))->toBe('{"preset": "psr12"}');
    } else {
        expect(File::exists(base_path('pint.json')))->toBeFalse();
    }
})->with([true, false]);

it('requires a Composer project before installing Pint', function () {
    File::delete(base_path('composer.json'));
    $this->mock(Composer::class)->shouldNotReceive('requirePackages');

    $this->artisan('default:pint')
        ->expectsOutputToContain('No composer.json found in the application root.')
        ->assertFailed();

    expect(File::exists(base_path('pint.json')))->toBeFalse();
});
