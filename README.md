<h1 align="center">Buzz - Scripted HTTP browser</h1>

<div align="center">

[![Build Status](https://img.shields.io/travis/kriswallsmith/Buzz/master.svg?style=flat-square)](https://travis-ci.org/kriswallsmith/Buzz)
[![Latest Version](https://img.shields.io/github/release/kriswallsmith/Buzz.svg?style=flat-square)](https://github.com/kriswallsmith/Buzz/releases)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/kriswallsmith/Buzz.svg?style=flat-square)](https://scrutinizer-ci.com/g/kriswallsmith/Buzz)
[![Quality Score](https://img.shields.io/scrutinizer/g/kriswallsmith/Buzz.svg?style=flat-square)](https://scrutinizer-ci.com/g/kriswallsmith/Buzz)
[![Total Downloads](https://img.shields.io/packagist/dt/kriswallsmith/buzz.svg?style=flat-square)](https://packagist.org/packages/kriswallsmith/buzz)
[![Monthly Downloads](https://img.shields.io/packagist/dm/kriswallsmith/buzz.svg?style=flat-square)](https://packagist.org/packages/kriswallsmith/buzz)

</div>

Buzz is a lightweight (<1000 lines of code) PHP 7.1 library for issuing HTTP requests.

## Installation

Install by running:

```bash
composer require kriswallsmith/buzz
```

You do also need to install a PSR-17 request/response factory. Buzz uses that factory
to create PSR-7 requests and responses. Install one from [this list](https://packagist.org/providers/psr/http-factory-implementation).

Example: 

```bash
composer require nyholm/psr7
```

## Usage

This page will just show you the basics, please [read the full documentation](doc/index.md).

```php
$client = new Buzz\Client\FileGetContents(new Psr17ResponseFactory());
$browser = new Buzz\Browser($client, new Psr17RequestFactory());
$response = $browser->get('http://www.google.com');

echo $browser->getLastRequest()."\n";
// $response is a PSR-7 object.
echo $response->getStatusCode();
```

You can also use the low-level HTTP classes directly.

```php
$request = new PSR7Request('GET', 'https://google.com/foo');

$client = new Buzz\Client\FileGetContents(new Psr17ResponseFactory());
$response = $client->send($request, ['timeout' => 4]);

echo $response->getStatusCode();
```

### Note

The two `new Psr17ResponseFactory()` and `new Psr17RequestFactory()` are placeholders 
for whatever PSR-17 factory you choose. If you use `nyholm/psr7` then the example above
would start like: 

```php
use \Nyholm\Psr7\Factory\Psr17Factory;

$client = new Buzz\Client\FileGetContents(new Psr17Factory());
$browser = new Buzz\Browser($client, new Psr17Factory());
$response = $browser->get('http://www.google.com');
```

## The Idea of Buzz

Buzz was created by Kris Wallsmith back in 2010. The project grown very popular over the years with more than 7 million
downloads.

Since August 2017 [Tobias Nyholm](http://tnyholm.se) is maintaining this library. The idea of Buzz will still be the same,
we should have a simple API and mimic browser behavior for easy testing. We should not reinvent the wheel and we should
not be as powerful and flexible as other clients (ie Guzzle). We do, however, take performance very seriously.

We do love PSRs and this is a wish list of what PSR we would like to support:

* PSR-1 (Code style)
* PSR-2 (Code style)
* PSR-4 (Auto loading)
* PSR-7 (HTTP messages)
* PSR-17 (HTTP factories)
* PSR-18 (HTTP client)

## Backwards Compatibility Promise

We take backwards compatibility very seriously as you should do with any open source project. We strictly follow [Semver](http://semver.org/).
Please note that Semver works a bit different prior version 1.0.0. Minor versions prior 1.0.0 are allow to break backwards
compatibility.

Being greatly inspired by [Symfony's bc promise](https://symfony.com/doc/current/contributing/code/bc.html), we have adopted
their method of deprecating classes, interfaces and functions.

## Running the tests

There are 2 kinds of tests for this library; unit tests and integration tests. They can be run separably by:

```bash
./vendor/bin/simple-phpunit --testsuite Unit
./vendor/bin/simple-phpunit --testsuite Integration
```

The integration tests makes real HTTP requests to a webserver. There are two different
webservers used by our integration tests. A real Nginx server and PHP's built in webserver.
The tests that runs with PHP's webserver are provided by `php-http/client-integration-tests`.

To start the server, open terminal A and run:

```bash
./vendor/bin/http_test_server
```

The other type of integration tests are using Nginx. We use Docker to start the
Nginx server.

```bash
docker build -t buzz/tests .
docker run -d -p 127.0.0.1:8022:80 buzz/tests
```

You are now ready to run the integration tests

```bash
./vendor/bin/simple-phpunit --testsuite Integration
```
