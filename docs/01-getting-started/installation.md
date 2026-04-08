# Installation

Install JOOservices Laravel Controller with Composer:

```bash
composer require jooservices/laravel-controller
```

To customize package behavior, publish the configuration file:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

If you want translated default messages, also publish the language files:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="laravel-controller-lang"
```