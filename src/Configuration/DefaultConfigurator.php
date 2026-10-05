<?php

declare(strict_types=1);

namespace MaioBarbero\LaravelAftercare\Configuration;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

final readonly class DefaultConfigurator
{
    public function __construct(private Application $app) {}

    public function apply(): void
    {
        $this
            ->whenEnabled('automatically_eager_load_relationships', Model::automaticallyEagerLoadRelationships(...))
            ->whenEnabled('force_https_in_production', static function (): void {
                URL::forceScheme('https');
            }, $this->app->isProduction())
            ->whenEnabled('immutable_dates', static function (): void {
                Date::use(CarbonImmutable::class);
            })
            ->whenEnabled('prevent_stray_requests_in_tests', Http::preventStrayRequests(...), $this->app->runningUnitTests())
            ->whenEnabled('prohibit_destructive_commands_in_production', DB::prohibitDestructiveCommands(...), $this->app->isProduction())
            ->whenEnabled('strict_models', Model::shouldBeStrict(...))
            ->whenEnabled('password_defaults.enabled', $this->configurePasswords(...));
    }

    /**
     * @param  callable(): mixed  $configure
     */
    private function whenEnabled(string $key, callable $configure, bool $environmentMatches = true): self
    {
        if ($environmentMatches && Config::boolean('aftercare.'.$key, true)) {
            $configure();
        }

        return $this;
    }

    private function configurePasswords(): void
    {
        Password::defaults(static fn (): Password => Password::min(Config::integer('aftercare.password_defaults.min', 12))
            ->when(
                Config::boolean('aftercare.password_defaults.mixed_case', true),
                static fn (Password $rule): Password => $rule->mixedCase(),
            )
            ->when(
                Config::boolean('aftercare.password_defaults.numbers', true),
                static fn (Password $rule): Password => $rule->numbers(),
            )
            ->when(
                Config::boolean('aftercare.password_defaults.symbols', true),
                static fn (Password $rule): Password => $rule->symbols(),
            ));
    }
}
