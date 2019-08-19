# Okta Provider for OAuth 2.0 Client
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/foxworth42/oauth2-okta.svg?style=flat-square)](https://packagist.org/packages/foxworth42/oauth2-okta)
[![Build Status](https://travis-ci.org/foxworth42/oauth2-okta.svg?branch=master)](https://travis-ci.org/foxworth42/oauth2-okta)

This package provides Okta OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require foxworth42/oauth2-okta
```

## Usage

Usage is the same as The League's OAuth client, using `\League\OAuth2\Client\Provider\Okta` as the provider.

You must configure the Issuer URI as the `issuer` parameter.  The issuer URI can be found in Okta's admin dashboard under API -> Authorization Servers. 

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/foxworth42/oauth2-okta/blob/master/LICENSE) for more information.
