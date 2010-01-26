<?php

namespace Buzz;

use Buzz\Message;

include __DIR__.'/../../bootstrap/unit.php';

$t = new \LimeTest(2);

// ->get()
$t->diag('->get()');

$browser = new Browser();
$response = $browser->get('http://www.google.com');
$t->is($response instanceof Message\Response, true, '->get() returns a response');

// ->getDom()
$t->diag('->getDom()');

$browser = new Browser();
$browser->get('http://www.google.com');
$document = $browser->getDom();
$t->is($document instanceof \DOMDocument, true, '->getDom() returns a DOMDocument');
