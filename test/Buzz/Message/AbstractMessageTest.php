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

        $this->assertEquals('foo'.PHP_EOL.'bar', $message->getHeader('X-My-Header'));
        $this->assertEquals('foo,bar', $message->getHeader('X-My-Header', ','));
        $this->assertEquals(array('foo', 'bar'), $message->getHeader('X-My-Header', false));
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

        $this->assertEquals($expected, (string) $message);
    }

    public function testGetHeaderAttributesReturnsHeaderAttributes()
    {
        $message = new Message();
        $message->addHeader('Content-Type: text/xml; charset=utf8');

        $this->assertEquals('utf8', $message->getHeaderAttribute('Content-Type', 'charset'));
    }
}
