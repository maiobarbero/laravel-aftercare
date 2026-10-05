<?php

declare(strict_types=1);

namespace MaioBarberoDefault\MaioBarberoDefault\Configuration;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

final class DefaultConfigurator
{
    public function __construct(private readonly Application $app) {}

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
        if ($environmentMatches && Config::boolean('default.'.$key, true)) {
            $configure();
        }

        return $this;
    }

    private function configurePasswords(): void
    {
        Password::defaults(static fn (): Password => Password::min(Config::integer('default.password_defaults.min', 12))
            ->when(
                Config::boolean('default.password_defaults.mixed_case', true),
                static fn (Password $rule): Password => $rule->mixedCase(),
            )
            ->when(
                Config::boolean('default.password_defaults.numbers', true),
                static fn (Password $rule): Password => $rule->numbers(),
            )
            ->when(
                Config::boolean('default.password_defaults.symbols', true),
                static fn (Password $rule): Password => $rule->symbols(),
            ));
    }
}
