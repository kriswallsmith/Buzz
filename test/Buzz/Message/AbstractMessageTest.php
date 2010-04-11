<?php

namespace Buzz\Message;

require_once __DIR__.'/../../../lib/Buzz/ClassLoader.php';
\Buzz\ClassLoader::register();

require_once 'PHPUnit/Framework/TestCase.php';

class Message extends AbstractMessage
{
}

class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
  public function testGetHeaderGluesHeadersTogether()
  {
    $message = new Message();
    $message->addHeader('X-My-Header: foo');
    $message->addHeader('X-My-Header: bar');

    $this->assertEquals($message->getHeader('X-My-Header'), 'foo'.PHP_EOL.'bar');
    $this->assertEquals($message->getHeader('X-My-Header', ','), 'foo,bar');
    $this->assertEquals($message->getHeader('X-My-Header', false), array('foo', 'bar'));
  }

  public function testGetHeaderReturnsNullIfHeaderDoesNotExist()
  {
    $message = new Message();

    $this->assertNull($message->getHeader('X-Nonexistant'));
  }

  public function testToStringFormatsTheMessage()
  {
    $message = new Message();
    $message->addHeader('Foo: Bar');
    $message->setContent('==CONTENT==');

    $expected = <<<EOF
Foo: Bar

==CONTENT==

EOF;

    $this->assertEquals((string) $message, $expected);
  }
}
