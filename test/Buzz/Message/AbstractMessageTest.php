<?php

namespace Buzz\Message;

include __DIR__.'/../../../bootstrap/unit.php';

$t = new \LimeTest(5);

class Message extends AbstractMessage
{
}

// ->getHeader()
$t->diag('->getHeader()');

$message = new Message();
$message->addHeader('X-My-Header: foo');
$message->addHeader('X-My-Header: bar');
$t->is($message->getHeader('X-My-Header'), 'foo'.PHP_EOL.'bar', '->getHeader() glues multiple header values together');
$t->is($message->getHeader('X-My-Header', ','), 'foo,bar', '->getHeader() accepts a custom glue value');
$t->is($message->getHeader('X-My-Header', false), array('foo', 'bar'), '->getHeader() returns an array if glue is false');
$t->is($message->getHeader('X-Nonexistant'), null, '->getHeader() returns null if header does not exist');

// ->__toString()
$t->diag('->__toString()');

$message = new Message();
$message->addHeader('Foo: Bar');
$message->setContent('==CONTENT==');
$expected = <<<EOF
Foo: Bar

==CONTENT==

EOF;
$t->is((string) $message, $expected, '->__toString() converts a message to a string');
