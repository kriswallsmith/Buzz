[![Build Status](https://secure.travis-ci.org/kriswallsmith/Buzz.png?branch=master)](http://travis-ci.org/kriswallsmith/Buzz)

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
