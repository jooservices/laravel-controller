# Installation

Install JOOservices Laravel Controller with Composer:

```bash
composer require jooservices/laravel-controller:^4.0
```

Migrating from v1.x: read [UPGRADE-4.0.md](../../UPGRADE-4.0.md).

To customize package behavior, publish the configuration file:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

If you want translated default messages, also publish the language files:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="laravel-controller-lang"
```