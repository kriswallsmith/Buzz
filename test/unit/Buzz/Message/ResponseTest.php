<?php

namespace Buzz\Message;

include __DIR__.'/../../../bootstrap/unit.php';

$t = new \LimeTest(3);

// ->getProtocolVersion() ->getStatusCode() ->getReasonPhrase()
$t->diag('->getProtocolVersion() ->getStatusCode() ->getReasonPhrase()');

$response = new Response();
$response->addHeader('1.0 200 OK');
$t->is($response->getProtocolVersion(), 1.0, '->getProtocolVersion() returns the protocol version');
$t->is($response->getStatusCode(), 200, '->getStatusCode() returns the status code');
$t->is($response->getReasonPhrase(), 'OK', '->getReasonPhrase() returns the reason phrase');
