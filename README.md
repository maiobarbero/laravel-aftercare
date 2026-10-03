<div align="center">
    <h1>MaioBarbero Default</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/maio-barbero/default"><img src="https://img.shields.io/packagist/v/maio-barbero/default.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/maio-barbero/default"><img src="https://img.shields.io/packagist/php-v/maio-barbero/default.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/maio-barbero/default"><img src="https://badge.laravel.cloud/badge/maio-barbero/default?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/maio-barbero/default/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/maio-barbero/default/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/maio-barbero/default"><img src="https://img.shields.io/packagist/dt/maio-barbero/default.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Default settings and packages to start a new Laravel Project

## Installation

You can install the package via Composer:

```bash
composer require maio-barbero/default
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="default"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="default-config"
```

## Usage

Generate an action class:

```bash
php artisan make:action CreateUser
```

This creates `app/Actions/CreateUser.php` as a final, readonly class with strict
types and an empty `handle(): void` method.

Add `--transaction` (or `-t`) to import the `DB` facade and wrap the method body
in `DB::transaction()`:

```bash
php artisan make:action CreateUser --transaction
php artisan make:action CreateUser -t
```

Use a nested name to organize actions into subdirectories:

```bash
php artisan make:action Users/CreateUser
```

Existing actions are preserved. Pass `--force` to overwrite an existing action.

### Set Up Pint

Run this command in your Laravel application:

```bash
php artisan default:pint
```

It runs Composer to install `laravel/pint` as a development dependency, then copies
the package's `stubs/pint.json.stub` to the application's root `pint.json`.
The package's own Pint dependency and root configuration are for internal use.

If `pint.json` already exists, the command stops before running Composer. Use
`--force` (or `-f`) to replace it with the package defaults. If Composer fails,
the configuration is left unchanged.

The configuration extends Laravel's preset with strict types, additional blank
lines before control statements, imports for global classes, trailing commas in
multiline lists, and one argument or parameter per line in multiline signatures
and calls. It also groups class members by kind and visibility, separates trait
imports, adds separators to large numbers, and removes empty attribute
parentheses. It simplifies redundant branches, boolean returns, null-coalescing
assignments, and repeated `isset`/`unset` operations; converts suitable callbacks
to arrow functions; and uses `array<Type>` in PHPDoc. Blade formatting is not enabled.

Run the formatter or check formatting without modifying files:

```bash
vendor/bin/pint
vendor/bin/pint --test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to MaioBarbero Default! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Matteo Barbero](https://github.com/maio-barbero)
- [All Contributors](../../contributors)

## License

MaioBarbero Default is open-sourced software licensed under the [MIT license](LICENSE.md).
