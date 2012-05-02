<?php

namespace Buzz\Test\Message;

use Buzz\Message\AbstractMessage;

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

        $this->assertEquals('foo'."\r\n".'bar', $message->getHeader('X-My-Header'));
        $this->assertEquals('foo,bar', $message->getHeader('X-My-Header', ','));
        $this->assertEquals(array('foo', 'bar'), $message->getHeader('X-My-Header', false));
    }

    public function testGetHeaderReturnsNullIfHeaderDoesNotExist()
    {
        $message = new Message();

        $this->assertNull($message->getHeader('X-Nonexistant'));
    }

    public function testGetHeaderIsCaseInsensitive()
    {
        $message = new Message();
        $message->addHeader('X-zomg: test');

        $this->assertEquals('test', $message->getHeader('X-ZOMG'));
    }

    public function testToStringFormatsTheMessage()
    {
        $message = new Message();
        $message->addHeader('Foo: Bar');
        $message->setContent('==CONTENT==');

        $expected = "Foo: Bar\r\n\r\n==CONTENT==\r\n";

        $this->assertEquals($expected, (string) $message);
    }

    public function testToStringFormatsTheMessageWithArrayContent()
    {
        $message = new Message();
        $message->addHeader('Foo: Bar');
        $message->setContent(array('baz'=>'baz', 'bat'=>'bat'));

        $expected = "Foo: Bar\r\n\r\nbaz=baz&bat=bat\r\n";

        $this->assertEquals($expected, (string) $message);
    }

    public function testToStringFormatsTheMessageWithObjectContent()
    {
        $message = new Message();
        $message->addHeader('Foo: Bar');
        $message->setContent((object) array('baz'=>'baz', 'bat'=>'bat'));

        $expected = "Foo: Bar\r\n\r\nbaz=baz&bat=bat\r\n";

        $this->assertEquals($expected, (string) $message);
    }

    public function testGetHeaderAttributesReturnsHeaderAttributes()
    {
        $message = new Message();
        $message->addHeader('Content-Type: text/xml; charset=utf8');

        $this->assertEquals('utf8', $message->getHeaderAttribute('Content-Type', 'charset'));
    }

    public function testGetNotFoundHeaderAttribute()
    {
        $message = new Message();
        $this->assertNull($message->getHeaderAttribute('Content-Type', 'charset'));
    }

    public function testAddHeaders()
    {
        $message = new Message();
        $message->addHeaders(array('Content-Type: text/xml; charset=utf8'));
        $this->assertEquals(1, count($message->getHeaders()));
    }

    public function testToDomDocument()
    {
        $message = new Message();
        $message->setContent('<foo><bar></bar></foo>');
        $this->assertInstanceOf('DOMDocument', $message->toDomDocument());
    }
}
