<?php

namespace Buzz\Test\Message;

use Buzz\Message\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorSetsMethodResourceAndHost()
    {
        $request = new Request('HEAD', '/resource/123', 'http://example.com');

        $this->assertEquals('HEAD', $request->getMethod());
        $this->assertEquals('/resource/123', $request->getResource());
        $this->assertEquals('http://example.com', $request->getHost());
    }

    public function testGetUrlFormatsAUrl()
    {
        $request = new Request();
        $request->setHost('http://example.com');
        $request->setResource('/resource/123');

        $this->assertEquals('http://example.com/resource/123', $request->getUrl());
    }

    public function testFromUrlSetsRequestValues()
    {
        $request = new Request();
        $request->fromUrl('http://example.com/resource/123?foo=bar#foobar');

        $this->assertEquals('http://example.com', $request->getHost());
        $this->assertEquals('/resource/123?foo=bar', $request->getResource());
    }

    public function testFromUrlSetsADefaultResource()
    {
        $request = new Request();
        $request->fromUrl('http://example.com');

        $this->assertEquals('/', $request->getResource());

        $request = new Request();
        $request->fromUrl('http://example.com?foo=bar');

        $this->assertEquals('/?foo=bar', $request->getResource());
    }

    public function testFromUrlSetsADefaultScheme()
    {
        $request = new Request();
        $request->fromUrl('example.com/foo/bar');

        $this->assertEquals('http://example.com', $request->getHost());
        $this->assertEquals('/foo/bar', $request->getResource());
    }

    public function testFromUrlLeaveHostEmptyIfNoneIsProvided()
    {
        $request = new Request();
        $request->fromUrl('/foo');

        $this->assertNull($request->getHost());
    }

    public function testFromUrlAcceptsPort()
    {
        $request = new Request();
        $request->fromUrl('http://localhost:3000/foo');

        $this->assertEquals('http://localhost:3000', $request->getHost());
        $this->assertEquals('/foo', $request->getResource());
    }

    public function testFromUrlRejectsInvalidUrl()
    {
        $this->setExpectedException('InvalidArgumentException');

        // port number is too high
        $request = new Request();
        $request->fromUrl('http://localhost:123456');
    }

    public function testIsSecureChecksScheme()
    {
        $request = new Request('GET', '/resource/123', 'http://example.com');
        $this->assertFalse($request->isSecure());

        $request = new Request('GET', '/resource/123', 'https://example.com');
        $this->assertTrue($request->isSecure());
    }

    public function testToStringFormatsTheRequest()
    {
        $request = new Request('POST', '/resource/123', 'http://example.com');
        $request->setProtocolVersion(1.1);
        $request->addHeader('Content-Type: application/x-www-form-urlencoded');
        $request->setContent('foo=bar&bar=baz');

        $expected  = "POST /resource/123 HTTP/1.1\r\n";
        $expected .= "Host: http://example.com\r\n";
        $expected .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $expected .= "\r\n";
        $expected .= "foo=bar&bar=baz\r\n";

        $this->assertEquals($expected, (string) $request);
    }

    public function testMethodIsAlwaysUppercased()
    {
        $request = new Request('post', '/resource/123', 'http://example.com');

        $this->assertEquals('POST', $request->getMethod());
    }

    public function testSetMethod()
    {
        $request = new Request();
        $request->setMethod('get');
        $this->assertEquals('GET', $request->getMethod());
    }

    public function testCookieMerge()
    {
        $request = new Request();
        $request->addHeader('Cookie: foo=bar');
        $request->addHeader('Content-Type: text/plain');
        $request->addHeader('Cookie: bar=foo');

        $this->assertEquals(array(
            'Cookie: foo=bar; bar=foo',
            'Content-Type: text/plain',
        ), $request->getHeaders());

        $expected  = "GET / HTTP/1.1\r\n";
        $expected .= "Cookie: foo=bar; bar=foo\r\n";
        $expected .= "Content-Type: text/plain\r\n";

        $this->assertEquals($expected, (string) $request);
    }
}
