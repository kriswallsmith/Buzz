<?php

declare(strict_types=1);
require '../vendor/autoload.php';

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Middleware\CookieMiddleware;

$psr17Factory = \Nyholm\Psr7\Factory\Psr17Factory();
$client = new Curl($psr17Factory);
$browser = new Browser($client, $psr17Factory);

// Create CookieListener
$middleware = new CookieMiddleware();
$browser->addMiddleware($middleware);

// This URL set two Cookies, k1=v1 and k2=v2
$response = $browser->get('http://httpbin.org/cookies/set?k1=v1&k2=v2');

// This URL will return the two set Cookies
$response = $browser->get('http://httpbin.org/cookies');
echo $response->getBody()->__toString();

// Should output
/*
{
  "cookies": {
    "k1": "v1",
    "k2": "v2"
  }
}
*/

// The Cookies are able to be retrieved and set using getCookies and setCookies on the Listener.
print_r($middleware->getCookies());
