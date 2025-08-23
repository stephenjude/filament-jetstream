![Edit Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/banner.jpg)

# Filament Jetstream: Filament Starter Kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stephenjude/filament-jetstream.svg?style=flat-square)](https://packagist.org/packages/stephenjude/filament-jetstream)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/stephenjude/filament-jetstream/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/stephenjude/filament-jetstream/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/stephenjude/filament-jetstream/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/stephenjude/filament-jetstream/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stephenjude/filament-jetstream.svg?style=flat-square)](https://packagist.org/packages/stephenjude/filament-jetstream)

A **Filament-first starter kit** for Laravel inspired by Laravel Jetstream.
It comes pre-packaged with authentication, user profiles, team management, and API tokens — all implemented using **native Filament panels and components**.

## Installation

You can install the package via composer:

```bash
composer require stephenjude/filament-jetstream

php artisan filament-jetstream:install --teams --api
```
You can remove the `--teams` and `--api` arguments if you don't want those features.

## Features

##### 🔐 Authentication
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/1.x/art/login.jpeg)

##### 👤 User Profile
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/1.x/art/profile.jpeg)

##### 👥 Team (Optional)
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/1.x/art/team.jpeg)

##### 🔑 API Tokens (Optional)
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/1.x/art/tokens.jpeg)

##### 🌍 Translation-ready

## Manual Installation
coming soon...

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
- [taylorotwell](https://github.com/taylorotwell)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
