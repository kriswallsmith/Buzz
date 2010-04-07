<?php

namespace Buzz\Browser;

use Buzz\Message;

include __DIR__.'/../../../bootstrap/unit.php';

$t = new \LimeTest(2);

$request1 = new Message\Request();
$request1->setContent('request1');
$request2 = new Message\Request();
$request2->setContent('request2');
$request3 = new Message\Request();
$request3->setContent('request3');

$response1 = new Message\Response();
$response1->setContent('response1');
$response2 = new Message\Response();
$response2->setContent('response2');
$response3 = new Message\Response();
$response3->setContent('response3');

// ->add() ->getLast()
$t->diag('->add() ->getLast()');

$history = new History();
$history->setLimit(2);
$history->add($request1, $response1);
$history->add($request2, $response2);
$history->add($request3, $response3);

$t->is(count($history), 2, '->add() respects the set limit');

list($request, $response) = $history->getLast();
$t->is($request->getContent(), 'request3', '->getLast() returns the last entry');
