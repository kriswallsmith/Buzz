<?php

namespace Buzz\Message;

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

        $expected = <<<EOF
POST /resource/123 HTTP/1.1
Host: http://example.com
Content-Type: application/x-www-form-urlencoded

foo=bar&bar=baz

EOF;

        $this->assertEquals($expected, (string) $request);
    }

    public function testMethodIsAlwaysUppercased()
    {
        $request = new Request('post', '/resource/123', 'http://example.com');

        $this->assertEquals('POST', $request->getMethod());
    }
}
