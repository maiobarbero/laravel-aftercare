<?php

declare(strict_types=1);

use MaioBarbero\LaravelAftercare\LaravelAftercare;

it('resolves the singleton', function () {
    expect(app(LaravelAftercare::class))->toBeInstanceOf(LaravelAftercare::class);
});

it('returns the same instance from the container', function () {
    expect(app(LaravelAftercare::class))->toBe(app(LaravelAftercare::class));
});

it('merges the package config', function () {
    expect(config('aftercare.strict_models'))->toBeTrue();
});
