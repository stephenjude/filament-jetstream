# Laravel Jetstream adapter for Filament Admin Panels

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stephenjude/filament-jetstream.svg?style=flat-square)](https://packagist.org/packages/stephenjude/filament-jetstream)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/stephenjude/filament-jetstream/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/stephenjude/filament-jetstream/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/stephenjude/filament-jetstream/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/stephenjude/filament-jetstream/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stephenjude/filament-jetstream.svg?style=flat-square)](https://packagist.org/packages/stephenjude/filament-jetstream)

This package adapts Laravel Jetstream Livewire into your filament admin panel, and it supports team, api and dark mode.

## Installation

You can install the package via composer:

```bash
composer require stephenjude/filament-jetstream
```

Install Jetstream with team, api and email verification.
```php
php artisan filament:jetstream:install --team --api --verification
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-jetstream-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-jetstream-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
php artisan 
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [stephenjude](https://github.com/stephenjude)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
