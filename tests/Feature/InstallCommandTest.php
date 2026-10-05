<?php

declare(strict_types=1);

use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use MaioBarbero\LaravelAftercare\AftercareServiceProvider;
use Symfony\Component\Console\Output\OutputInterface;

beforeEach(function (): void {
    $this->app->getNamespace();
    $path = sys_get_temp_dir().'/aftercare-install-'.bin2hex(random_bytes(8));
    File::ensureDirectoryExists($path);
    $this->app->setBasePath($path);
    $this->app->useConfigPath($path.'/config');
    $this->app->useAppPath($path.'/app');
    File::put(base_path('composer.json'), '{"require": {"php": "^8.3"}}');
    (new AftercareServiceProvider($this->app))->boot();
});

afterEach(function (): void {
    File::deleteDirectory(base_path());
});

it('sets up all tools and publishes editable configuration and stubs', function (): void {
    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->times(3)->with(base_path())->andReturnSelf();

    foreach ([['laravel/pint'], ['phpstan/phpstan', 'larastan/larastan'], ['rector/rector']] as $packages) {
        $composer->shouldReceive('requirePackages')->once()->ordered()
            ->with([...$packages, '--no-interaction'], true, Mockery::type(OutputInterface::class))
            ->andReturnTrue();
    }

    $this->artisan('aftercare:install')->assertSuccessful();

    foreach (['pint.json', 'phpstan.neon', 'rector.php'] as $file) {
        expect(File::get(base_path($file)))->toBe(File::get(__DIR__.'/../../stubs/'.$file.'.stub'));
    }

    expect(File::get(config_path('aftercare.php')))->toBe(File::get(__DIR__.'/../../config/aftercare.php'));

    foreach (File::files(__DIR__.'/../../stubs') as $stub) {
        expect(File::get(base_path('stubs/aftercare/'.$stub->getFilename())))->toBe($stub->getContents());
    }
});

it('checks for conflicts before running any installer or publishing files', function (string $file): void {
    File::ensureDirectoryExists(dirname(base_path($file)));
    File::put(base_path($file), 'Existing configuration');
    $this->mock(Composer::class)->shouldNotReceive('requirePackages');

    $this->artisan('aftercare:install')->expectsOutputToContain('already exists')->assertFailed();

    expect(File::get(base_path($file)))->toBe('Existing configuration');
    expect(File::exists(base_path('pint.json')))->toBeFalse();
    expect(File::exists(base_path('stubs/aftercare')))->toBeFalse();
})->with(['rector.php', 'phpstan.neon.dist', 'config/aftercare.php']);

it('stops setup when an installer fails', function (): void {
    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->twice()->with(base_path())->andReturnSelf();
    $composer->shouldReceive('requirePackages')->once()->ordered()
        ->with(['laravel/pint', '--no-interaction'], true, Mockery::type(OutputInterface::class))
        ->andReturnTrue();
    $composer->shouldReceive('requirePackages')->once()->ordered()
        ->with(['phpstan/phpstan', 'larastan/larastan', '--no-interaction'], true, Mockery::type(OutputInterface::class))
        ->andReturnFalse();

    $this->artisan('aftercare:install')->expectsOutputToContain('PHPStan installation failed')->assertFailed();

    expect(File::exists(base_path('pint.json')))->toBeTrue();
    expect(File::exists(base_path('phpstan.neon')))->toBeFalse();
    expect(File::exists(base_path('rector.php')))->toBeFalse();
    expect(File::exists(config_path('aftercare.php')))->toBeFalse();
});

it('uses published stubs and preserves them when forcing a complete setup', function (): void {
    $this->artisan('vendor:publish', ['--tag' => 'aftercare-stubs'])->assertSuccessful();

    foreach (['pint.json', 'phpstan.neon', 'rector.php'] as $file) {
        File::put(base_path('stubs/aftercare/'.$file.'.stub'), 'Customized '.$file);
        File::put(base_path($file), 'Existing configuration');
    }

    File::ensureDirectoryExists(config_path());
    File::put(config_path('aftercare.php'), '<?php return [];');
    $composer = $this->mock(Composer::class);
    $composer->shouldReceive('setWorkingPath')->times(3)->with(base_path())->andReturnSelf();
    $composer->shouldReceive('requirePackages')->times(3)->andReturnTrue();

    $this->artisan('aftercare:install', ['-f' => true])->assertSuccessful();

    foreach (['pint.json', 'phpstan.neon', 'rector.php'] as $file) {
        expect(File::get(base_path($file)))->toBe('Customized '.$file);
        expect(File::get(base_path('stubs/aftercare/'.$file.'.stub')))->toBe('Customized '.$file);
    }

    expect(File::get(config_path('aftercare.php')))->toBe(File::get(__DIR__.'/../../config/aftercare.php'));

    foreach (['action.stub', 'action.transaction.stub'] as $file) {
        File::put(base_path('stubs/aftercare/'.$file), '<?php namespace {{ namespace }}; class {{ class }} { /* Custom */ }');
    }

    $this->artisan('make:action', ['name' => 'CreateUser'])->assertSuccessful();
    $this->artisan('make:action', ['name' => 'UpdateUser', '-t' => true])->assertSuccessful();

    expect(File::get(app_path('Actions/CreateUser.php')))->toContain('class CreateUser { /* Custom */ }');
    expect(File::get(app_path('Actions/UpdateUser.php')))->toContain('class UpdateUser { /* Custom */ }');
});
