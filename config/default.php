<?php

declare(strict_types=1);

return [

    // Batch-load accessed relationships to reduce N+1 queries (Laravel 12.8+).
    'automatically_eager_load_relationships' => true,

    // Generate HTTPS URLs in production to avoid insecure links.
    'force_https_in_production' => true,

    // Use CarbonImmutable for Laravel dates to prevent accidental mutation.
    'immutable_dates' => true,

    // Block unfaked HTTP requests in tests to prevent accidental network calls.
    'prevent_stray_requests_in_tests' => true,

    // Block destructive database commands in production to prevent data loss.
    'prohibit_destructive_commands_in_production' => true,

    // Catch lazy loading, discarded attributes and missing attributes early.
    'strict_models' => true,

    // Share one password policy wherever Password::defaults() is used.
    'password_defaults' => [
        // Disable to keep the application's own password policy.
        'enabled' => true,

        // Require enough characters to resist guessing.
        'min' => 12,

        // Require upper- and lowercase letters for more varied passwords.
        'mixed_case' => true,

        // Require a digit to increase password variety.
        'numbers' => true,

        // Require a symbol to increase password variety.
        'symbols' => true,
    ],

];
