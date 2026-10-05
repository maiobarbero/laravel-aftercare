# Release Notes

## v0.1.0 - 2026-10-05

### Laravel Aftercare - v0.1.0

First release.

#### Highlights

* Add opinionated configuration for Pint, Rector, PHPStan
* Add configuration file to set up default Laravel app behavior
* 

#### Installation

```bash
composer require maiobarbero/laravel-aftercare
php artisan aftercare:install

```
## Unreleased

- Install Pint, PHPStan with Larastan, and Rector through `aftercare:install` or individual commands.
- Configure application defaults for relationships, HTTPS, dates, HTTP requests, destructive commands, passwords, and strict models.
- Generate action classes with optional database transactions.
- Publish configuration and override all five stubs in the application.
