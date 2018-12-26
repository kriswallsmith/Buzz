<?php

use Buzz\Client\MultiCurl;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;

require __DIR__.'/vendor/autoload.php';


$client = new MultiCurl(new Psr17Factory());

$client->sendAsyncRequest($a = new Request('GET', 'http://httpbin.org/delay/1'), ['callback' => function($request, $response, $exception) {
    var_dump('A');
    var_dump(time() - $_SERVER['REQUEST_TIME_FLOAT']);
    // echo $response->getBody()->getContents();
}]);

$client->sendAsyncRequest($a = new Request('GET', 'http://httpbin.org/delay/2'), ['callback' => function($request, $response, $exception) {
    var_dump('B');
    var_dump(time() - $_SERVER['REQUEST_TIME_FLOAT']);
    // echo $response->getBody()->getContents();
}]);
$client->sendAsyncRequest($a = new Request('GET', 'http://httpbin.org/delay/3'), ['callback' => function($request, $response, $exception) {
    var_dump('C');
    var_dump(time() - $_SERVER['REQUEST_TIME_FLOAT']);
    // echo $response->getBody()->getContents();
}]);

while (true) {
    $client->proceed();
    echo '.';
    usleep(100000);
}