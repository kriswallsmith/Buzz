<?php

require('../vendor/autoload.php');

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Listener\DigestAuthListener;

$username = 'user1';
$password = 'user1';

//
// This URL will Authenticate usernames user1 through user9, password is the same as the username.
//
$url = 'http://test.webdav.org/auth-digest/';

// Create Curl Client
$curl = new Curl();

$browser = new Browser();
$browser->setClient($curl);

// Create DigestAuthListener
$browser->addListener(new DigestAuthListener($username, $password));

//
// This URL will Authenticate any username and password that matches those in the URL.
// It requires the Browser/Client to respond with the same Cookie in order to Authenticate.
//
//	$url = 'http://httpbin.org/digest-auth/auth-int/' . $username . '/' . $password;

$response = $browser->get($url);
echo $response;

$statusCode = $response->getStatusCode();
if($statusCode == 401) {
    $response = $browser->get($url);
}

echo $response;
