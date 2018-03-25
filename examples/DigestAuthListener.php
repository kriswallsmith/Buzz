<?php

declare(strict_types=1);
require '../vendor/autoload.php';

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Middleware\DigestAuthMiddleware;

$username = 'user1';
$password = 'pass1';

//
// This URL will Authenticate usernames user1 through user9, password is the same as the username.
//
$url = 'http://test.webdav.org/auth-digest/';

// Create Curl Client
$curl = new Curl();

$browser = new Browser($curl);

// Create DigestAuthListener
$browser->addMiddleware(new DigestAuthMiddleware($username, $password));

//
// This URL will Authenticate any username and password that matches those in the URL.
// It requires the Browser/Client to respond with the same Cookie in order to Authenticate.
//
//	$url = 'http://httpbin.org/digest-auth/auth-int/' . $username . '/' . $password;

$response = $browser->get($url);
echo $response->getBody()->__toString();

$statusCode = $response->getStatusCode();
if (401 == $statusCode) {
    $response = $browser->get($url);
}

echo $response->getBody()->__toString();
