<?php

declare(strict_types=1);

use MaioBarberoDefault\MaioBarberoDefault\MaioBarberoDefault;

it('resolves the singleton', function () {
    expect(app(MaioBarberoDefault::class))->toBeInstanceOf(MaioBarberoDefault::class);
});

it('returns the same instance from the container', function () {
    expect(app(MaioBarberoDefault::class))->toBe(app(MaioBarberoDefault::class));
});

it('merges the package config', function () {
    expect(config('default.strict_models'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('default:placeholder')
        ->expectsOutputToContain('MaioBarberoDefault placeholder command executed.')
        ->assertSuccessful();
});
