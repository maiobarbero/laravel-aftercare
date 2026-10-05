<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->app->getNamespace();
    $this->app->useAppPath(sys_get_temp_dir().'/aftercare-actions-'.bin2hex(random_bytes(8)));
});

afterEach(function (): void {
    File::deleteDirectory(app_path());
});

it('generates an action without a transaction by default', function (string $name, string $path, string $namespace): void {
    $this->artisan('make:action', ['name' => $name])
        ->assertSuccessful();

    expect(File::get(app_path($path)))->toBe(<<<PHP
    <?php

    declare(strict_types=1);

    namespace {$namespace};

    final readonly class CreateUser
    {
        public function handle(): void
        {
            //
        }
    }

    PHP);
})->with([
    'simple name' => ['CreateUser', 'Actions/CreateUser.php', 'App\\Actions'],
    'nested name' => ['Users/CreateUser', 'Actions/Users/CreateUser.php', 'App\\Actions\\Users'],
    'fully qualified name' => ['App\\Actions\\Users\\CreateUser', 'Actions/Users/CreateUser.php', 'App\\Actions\\Users'],
]);

it('generates an action with a transaction when requested', function (string $option): void {
    $this->artisan('make:action', ['name' => 'Users/CreateUser', $option => true])
        ->assertSuccessful();

    expect(File::get(app_path('Actions/Users/CreateUser.php')))->toBe(<<<'PHP'
    <?php

    declare(strict_types=1);

    namespace App\Actions\Users;

    use Illuminate\Support\Facades\DB;

    final readonly class CreateUser
    {
        public function handle(): void
        {
            DB::transaction(function (): void {
                //
            });
        }
    }

    PHP);
})->with(['--transaction', '-t']);

it('preserves an existing action', function (): void {
    File::ensureDirectoryExists(app_path('Actions'));
    File::put(app_path('Actions/CreateUser.php'), '<?php // Existing action');

    $this->artisan('make:action', ['name' => 'CreateUser'])
        ->expectsOutputToContain('Action already exists.');

    expect(File::get(app_path('Actions/CreateUser.php')))->toBe('<?php // Existing action');
});

it('overwrites an existing action when forced', function (): void {
    File::ensureDirectoryExists(app_path('Actions'));
    File::put(app_path('Actions/CreateUser.php'), '<?php // Existing action');

    $this->artisan('make:action', ['name' => 'CreateUser', '--force' => true])
        ->assertSuccessful();

    expect(File::get(app_path('Actions/CreateUser.php')))
        ->toContain('final readonly class CreateUser')
        ->not->toContain('Existing action')
        ->not->toContain('DB::transaction');
});
