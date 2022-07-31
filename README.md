
# Laravel Packages View Extractor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wychoong/laravel-view-extract.svg?style=flat-square)](https://packagist.org/packages/wychoong/laravel-view-extract)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/wychoong/laravel-view-extract/run-tests?label=tests)](https://github.com/wychoong/laravel-view-extract/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/wychoong/laravel-view-extract/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/wychoong/laravel-view-extract/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wychoong/laravel-view-extract.svg?style=flat-square)](https://packagist.org/packages/wychoong/laravel-view-extract)

This package helps to extract view from vendor package using artisan command
## Installation

You can install the package via composer:

```bash
composer require wychoong/laravel-view-extract
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="view-extract-config"
```

This is the contents of the published config file:

```php
return [
    /**
     * Exclude views when resync views (when using sync all)
     */
    'exclude' => [
        // 'namespace::foo.bar.blade-name'
    ],

    /**
     * Only sync these views (when using sync all)
     *     - `only` take priority over `exclude
     *     - if same view listed in `exclude` it will still be excluded
     */
    'only' => [
        // 'namespace::foo.bar.blade-name'
    ],
];
```

## Usage

```bash
# Extract view from vendor
php artisan view:extract {view} {--force}

# Sync extracted views from vendor
php artisan view:sync {namespace?} {--check}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/wychoong/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [wychoong](https://github.com/wychoong)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
