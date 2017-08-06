[![Build Status](https://secure.travis-ci.org/kriswallsmith/Buzz.png?branch=master)](http://travis-ci.org/kriswallsmith/Buzz)

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
