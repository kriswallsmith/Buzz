<?php

namespace Buzz\Client\Mock;

use Buzz\Message;

include __DIR__.'/../../../../bootstrap/unit.php';

$t = new \LimeTest(1);

$response1 = new Message\Response();
$response1->setContent('first');

$response2 = new Message\Response();
$response2->setContent('last');

$client = new LIFO();
$client->sendToQueue($response1);
$client->sendToQueue($response2);

$request = new Message\Request();
$response = new Message\Response();
$client->send($request, $response);

$t->is($response->getContent(), 'last', '->send() uses the last queued response');
