<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use MaioBarbero\LaravelAftercare\AftercareServiceProvider;

afterEach(function () {
    Model::shouldBeStrict(false);

    Model::automaticallyEagerLoadRelationships(false);

    Date::useDefault();
    DB::prohibitDestructiveCommands(false);
    Password::$defaultCallback = null;
});

it('applies the model, date, HTTP and password defaults when the application boots', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();
    expect(Model::preventsSilentlyDiscardingAttributes())->toBeTrue();
    expect(Model::preventsAccessingMissingAttributes())->toBeTrue();

    expect(Model::isAutomaticallyEagerLoadingRelationships())->toBeTrue();

    expect(Date::now())->toBeInstanceOf(CarbonImmutable::class);
    expect(Http::preventingStrayRequests())->toBeTrue();
    expect(URL::to('/example'))->toStartWith('http://');

    foreach (['Short1!', 'lowercase123!', 'UPPERCASE123!', 'NoNumbersHere!', 'NoSymbols1234'] as $password) {
        expect(Validator::make(['password' => $password], ['password' => Password::defaults()])->fails())->toBeTrue();
    }

    expect(Validator::make(['password' => 'ValidPassword123!'], ['password' => Password::defaults()])->passes())->toBeTrue();
});

it('forces HTTPS and prohibits destructive commands only in production', function () {
    $this->app['env'] = 'production';
    Http::swap(new Factory);

    (new AftercareServiceProvider($this->app))->boot();

    expect(URL::to('/example'))->toStartWith('https://');
    expect(Http::preventingStrayRequests())->toBeFalse();

    $this->artisan('db:wipe', ['--force' => true, '--database' => 'nonexistent-test-connection'])
        ->expectsOutputToContain('This command is prohibited from running in this environment.')
        ->assertFailed();
});

it('allows the defaults to be disabled without replacing application settings', function () {
    $this->app['env'] = 'production';
    Model::shouldBeStrict(false);

    Model::automaticallyEagerLoadRelationships(false);

    Date::useDefault();
    Http::preventStrayRequests(false);
    Password::defaults(fn (): Password => Password::min(6));

    config([
        'aftercare.automatically_eager_load_relationships' => false,
        'aftercare.force_https_in_production' => false,
        'aftercare.immutable_dates' => false,
        'aftercare.prevent_stray_requests_in_tests' => false,
        'aftercare.prohibit_destructive_commands_in_production' => false,
        'aftercare.strict_models' => false,
        'aftercare.password_defaults.enabled' => false,
    ]);

    (new AftercareServiceProvider($this->app))->boot();

    expect(Model::preventsLazyLoading())->toBeFalse();
    expect(Model::preventsSilentlyDiscardingAttributes())->toBeFalse();
    expect(Model::preventsAccessingMissingAttributes())->toBeFalse();

    expect(Model::isAutomaticallyEagerLoadingRelationships())->toBeFalse();

    expect(Date::now())->not->toBeInstanceOf(CarbonImmutable::class);
    expect(Http::preventingStrayRequests())->toBeFalse();
    expect(URL::to('/example'))->toStartWith('http://');
    expect(Validator::make(['password' => 'simple'], ['password' => Password::defaults()])->passes())->toBeTrue();
});

it('uses the configured password requirements', function () {
    config([
        'aftercare.password_defaults.min' => 16,
        'aftercare.password_defaults.mixed_case' => false,
        'aftercare.password_defaults.numbers' => false,
        'aftercare.password_defaults.symbols' => false,
    ]);

    expect(Validator::make(['password' => 'longerpassphrase'], ['password' => Password::defaults()])->passes())->toBeTrue();
    expect(Validator::make(['password' => 'ValidPass123!'], ['password' => Password::defaults()])->fails())->toBeTrue();
});
