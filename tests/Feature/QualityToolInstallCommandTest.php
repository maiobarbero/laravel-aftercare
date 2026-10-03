<?php

declare(strict_types=1);

use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\OutputInterface;

dataset('quality tools', [
    'PHPStan' => ['default:phpstan', 'PHPStan', 'phpstan.neon', ['phpstan/phpstan', 'larastan/larastan']],
    'Rector' => ['default:rector', 'Rector', 'rector.php', ['rector/rector']],
]);

beforeEach(function () {
    $path = sys_get_temp_dir().'/default-quality-'.bin2hex(random_bytes(8));
    File::ensureDirectoryExists($path);
    $this->app->setBasePath($path);
    File::put(base_path('composer.json'), '{"require": {"php": "^8.3"}}');
});

afterEach(function () {
    File::deleteDirectory(base_path());
});

it('installs the tool as a development dependency before copying its stub', function (string $command, string $tool, string $file, array $packages) {
    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->once()->with(base_path())->andReturnSelf();
    $composer->shouldReceive('requirePackages')->once()
        ->with([...$packages, '--no-interaction'], true, Mockery::type(OutputInterface::class))
        ->andReturnUsing(function () use ($file): bool {
            expect(File::exists(base_path($file)))->toBeFalse();

            return true;
        });

    $this->artisan($command)
        ->expectsOutputToContain("{$tool} installed and {$file} created.")
        ->assertSuccessful();

    expect(File::get(base_path($file)))
        ->toBe(File::get(__DIR__.'/../../stubs/'.$file.'.stub'));
})->with('quality tools');

it('preserves existing configuration without running Composer', function (string $command, string $tool, string $file) {
    File::put(base_path($file), 'Existing configuration');
    $this->mock(Composer::class)->shouldNotReceive('requirePackages');

    $this->artisan($command)
        ->expectsOutputToContain("{$file} already exists. Use --force to replace it.")
        ->assertFailed();

    expect(File::get(base_path($file)))->toBe('Existing configuration');
})->with('quality tools');

it('replaces existing configuration when forced', function (string $command, string $tool, string $file, array $packages, string $option) {
    File::put(base_path($file), 'Existing configuration');
    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->once()->with(base_path())->andReturnSelf();
    $composer->shouldReceive('requirePackages')->once()
        ->with([...$packages, '--no-interaction'], true, Mockery::type(OutputInterface::class))
        ->andReturnTrue();

    $this->artisan($command, [$option => true])->assertSuccessful();

    expect(File::get(base_path($file)))
        ->toBe(File::get(__DIR__.'/../../stubs/'.$file.'.stub'));
})->with('quality tools')->with(['--force', '-f']);

it('leaves configuration unchanged if Composer fails', function (string $command, string $tool, string $file, array $packages, bool $existingConfiguration) {
    if ($existingConfiguration) {
        File::put(base_path($file), 'Existing configuration');
    }

    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->once()->with(base_path())->andReturnSelf();
    $composer->shouldReceive('requirePackages')->once()
        ->with([...$packages, '--no-interaction'], true, Mockery::type(OutputInterface::class))
        ->andReturnFalse();

    $this->artisan($command, ['--force' => true])
        ->expectsOutputToContain("{$tool} installation failed. Configuration was not changed.")
        ->assertFailed();

    if ($existingConfiguration) {
        expect(File::get(base_path($file)))->toBe('Existing configuration');
    } else {
        expect(File::exists(base_path($file)))->toBeFalse();
    }
})->with('quality tools')->with([true, false]);

it('requires a Composer project before installing a tool', function (string $command, string $tool, string $file) {
    File::delete(base_path('composer.json'));
    $this->mock(Composer::class)->shouldNotReceive('requirePackages');

    $this->artisan($command)
        ->expectsOutputToContain('No composer.json found in the application root.')
        ->assertFailed();

    expect(File::exists(base_path($file)))->toBeFalse();
})->with('quality tools');

it('does not shadow an existing PHPStan distribution configuration', function (string $file) {
    File::put(base_path($file), 'Existing configuration');
    $this->mock(Composer::class)->shouldNotReceive('requirePackages');

    $this->artisan('default:phpstan')
        ->expectsOutputToContain("{$file} already exists. Use --force to create phpstan.neon.")
        ->assertFailed();

    expect(File::get(base_path($file)))->toBe('Existing configuration');
    expect(File::exists(base_path('phpstan.neon')))->toBeFalse();
})->with(['phpstan.neon.dist', 'phpstan.dist.neon']);
