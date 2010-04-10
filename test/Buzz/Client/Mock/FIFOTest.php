<?php

namespace Buzz\Client\Mock;

use Buzz\Message;

include __DIR__.'/../../../../bootstrap/unit.php';

$t = new \LimeTest(3);

$response = new Message\Response();
$response->addHeader('HTTP/1.0 200 OK');
$response->setContent('Hello World!');

$client = new FIFO();
$client->sendToQueue($response);

$request = new Message\Request();
$response = new Message\Response();
$client->send($request, $response);

$t->is($response->getHeaders(), array('HTTP/1.0 200 OK'), '->send() sets response headers');
$t->is($response->getContent(), 'Hello World!', '->send() sets response content');

try
{
  $request = new Message\Request();
  $response = new Message\Response();
  $client->send($request, $response);

  $t->fail('->send() throws an exception if the queue is empty');
}
catch (\LogicException $e)
{
  $t->pass('->send() throws an exception if the queue is empty');
}
