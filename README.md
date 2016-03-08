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

Use Cid to track request from origin to end. 
Generate Cid Token:

```php
$cidObj = new Buzz\Client\Cid();
$cid = $cidObj->generateCid();

Add this token to the headers in before request filter, So that the target receives this CID as header parameter.

For making any further http request use the same CID as generated above and pass that as header parameter.

If http request is made without Cid in the headers, the Buzz library will add the Cid.
```
