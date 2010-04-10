<?php

namespace Buzz\History;

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

// ->record() ->getLast()
$t->diag('->record() ->getLast()');

$journal = new Journal();
$journal->setLimit(2);
$journal->record($request1, $response1);
$journal->record($request2, $response2);
$journal->record($request3, $response3);

$t->is(count($journal), 2, '->record() respects the set limit');
$t->is($journal->getLast()->getRequest()->getContent(), 'request3', '->getLast() returns the last entry');
