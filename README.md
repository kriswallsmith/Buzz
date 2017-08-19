<h1 align="center">Buzz - Scripted HTTP browser</h1>

<div align="center">

[![Build Status](https://img.shields.io/travis/kriswallsmith/Buzz.svg?branch=master&style=flat-square)](https://travis-ci.org/kriswallsmith/Buzz)
[![Latest Version](https://img.shields.io/github/release/kriswallsmith/Buzz.svg?style=flat-square)](https://github.com/kriswallsmith/Buzz/releases)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/kriswallsmith/Buzz.svg?style=flat-square)](https://scrutinizer-ci.com/g/kriswallsmith/Buzz)
[![Quality Score](https://img.shields.io/scrutinizer/g/kriswallsmith/Buzz.svg?style=flat-square)](https://scrutinizer-ci.com/g/kriswallsmith/Buzz)
[![Total Downloads](https://img.shields.io/packagist/dt/kriswallsmith/buzz.svg?style=flat-square)](https://packagist.org/packages/kriswallsmith/buzz)
[![Monthly Downloads](https://img.shields.io/packagist/dm/kriswallsmith/buzz.svg?style=flat-square)](https://packagist.org/packages/kriswallsmith/buzz)

</div>

Buzz is a lightweight PHP 5.3 library for issuing HTTP requests.

## Instalation

Package available on [Composer](https://packagist.org/packages/kriswallsmith/buzz).

If you're using Composer to manage dependencies, you can use

    $ composer require "kriswallsmith/buzz"

## Usage 

```php
<?php

$browser = new Buzz\Browser();
$response = $browser->get('http://www.google.com');

echo $browser->getLastRequest()."\n";
// $response is an object. 
// You can use $response->getContent() or $response->getHeaders() to get only one part of the response.
echo $response; 
```

You can also use the low-level HTTP classes directly.

```php
<?php

$request = new Buzz\Message\Request('HEAD', '/', 'http://google.com');
$response = new Buzz\Message\Response();

$client = new Buzz\Client\FileGetContents();
$client->send($request, $response);

echo $request;
echo $response;
```

## The Idea of Buzz

Buzz was created by Kris Wallsmith back in 2010. The project grown very popular over the years with more than 7 million 
downloads.  

Since August 2017 Tobias Nyholm is maintaining this library. The idea of Buzz will still be the same, we should have a
simple API and mimic browser behavior for easy testing. We should not reinvent the wheel and we should not be as powerful
and flexible as other clients (ie Guzzle). We do, however, take performance very seriously. 

We do love PSRs and this is a wish list of what PSR we would like to support: 

* PSR-1 (Code style)
* PSR-2 (Code style)
* PSR-4 (Auto loading)
* PSR-7 (HTTP messages)
* PSR-15 (HTTP middlewares)
* PSR-17 (HTTP factories)
* PSR-18 (HTTP client)

## Backwards Compatibility Promise

We take backwards compatibility very seriously as you should do with any open source project. We strictly follow [Semver](http://semver.org/).
Please note that Semver works a bit different prior version 1.0.0. Minor versions prior 1.0.0 are allow to break backwards
compatibility. 

Being greatly inspired by [Symfony's bc promise](https://symfony.com/doc/current/contributing/code/bc.html), we have adopted
their method of deprecating classes, interfaces and functions. We also promise that no code or features will be removed 
before 1.0.0.  