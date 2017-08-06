<h1 align="center">Buzz - HTTP client made simple</h1>

<div align="center">

[![Build Status](https://img.shields.io/travis/kriswallsmith/Buzz.svg?branch=master&style=flat-square)](https://travis-ci.org/kriswallsmith/Buzz)
[![Latest Version](https://img.shields.io/github/release/kriswallsmith/Buzz.svg?style=flat-square)](https://github.com/kriswallsmith/Buzz/releases)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/kriswallsmith/Buzz.svg?style=flat-square)](https://scrutinizer-ci.com/g/kriswallsmith/Buzz)
[![Quality Score](https://img.shields.io/scrutinizer/g/kriswallsmith/Buzz.svg?style=flat-square)](https://scrutinizer-ci.com/g/kriswallsmith/Buzz)
[![Total Downloads](https://img.shields.io/packagist/dt/kriswallsmith/buzz.svg?style=flat-square)](https://packagist.org/packages/kriswallsmith/buzz)

</div>

Buzz is a lightweight PHP 5.3 library for issuing HTTP requests.

```php
<?php

$browser = new Buzz\Browser();
$response = $browser->get('http://www.google.com');

echo $browser->getLastRequest()."\n";
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

## Installation

Use composer: 

```
composer require kriswallsmith/buzz
```

## The idea of Buzz

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
* PSR-17 (HTTP factories)
* PSR-18 (HTTP client)