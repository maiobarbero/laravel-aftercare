<?php

declare(strict_types=1);

use MaioBarbero\LaravelAftercare\LaravelAftercare;

it('resolves the singleton', function (): void {
    expect(app(LaravelAftercare::class))->toBeInstanceOf(LaravelAftercare::class);
});

it('returns the same instance from the container', function (): void {
    expect(app(LaravelAftercare::class))->toBe(app(LaravelAftercare::class));
});

it('merges the package config', function (): void {
    expect(config('aftercare.strict_models'))->toBeTrue();
});
