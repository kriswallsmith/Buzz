<?php

namespace Buzz\Message;

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

    public function testGetHeaderAttributesReturnsHeaderAttributes()
    {
        $message = new Message();
        $message->addHeader('Content-Type: text/xml; charset=utf8');

        $this->assertEquals($message->getHeaderAttribute('Content-Type', 'charset'), 'utf8');
    }

    public function testRemoveHeaderRemovesHeader()
    {
        $message = new Message();
        $message->addHeader('Content-Type: text/xml');
        $message->removeHeader('Content-Type');

        $this->assertNull($message->getHeader('Content-Type'));

        $message->addHeader('Content-Type: text/xml');
        $message->removeHeader('content-type');

        $this->assertNull($message->getHeader('Content-Type'));
    }
}
