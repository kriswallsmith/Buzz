<?php

namespace Buzz\Message;

require_once __DIR__.'/../../../lib/Buzz/ClassLoader.php';
\Buzz\ClassLoader::register();

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorSetsMethodResourceAndHost()
    {
        $request = new Request('HEAD', '/resource/123', 'http://example.com');

        $this->assertEquals($request->getMethod(), 'HEAD');
        $this->assertEquals($request->getResource(), '/resource/123');
        $this->assertEquals($request->getHost(), 'http://example.com');
    }

    public function testGetUrlFormatsAUrl()
    {
        $request = new Request();
        $request->setHost('http://example.com');
        $request->setResource('/resource/123');

        $this->assertEquals($request->getUrl(), 'http://example.com/resource/123');
    }

    public function testFromUrlSetsRequestValues()
    {
        $request = new Request();
        $request->fromUrl('http://example.com/resource/123?foo=bar#foobar');

        $this->assertEquals($request->getHost(), 'http://example.com');
        $this->assertEquals($request->getResource(), '/resource/123?foo=bar');
    }

    public function testFromUrlSetsADefaultResource()
    {
        $request = new Request();
        $request->fromUrl('http://example.com');

        $this->assertEquals($request->getResource(), '/');

        $request = new Request();
        $request->fromUrl('http://example.com?foo=bar');

        $this->assertEquals($request->getResource(), '/?foo=bar');
    }

    public function testFromUrlSetsADefaultScheme()
    {
        $request = new Request();
        $request->fromUrl('example.com/foo/bar');

        $this->assertEquals($request->getHost(), 'http://example.com');
        $this->assertEquals($request->getResource(), '/foo/bar');
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

        $this->assertEquals($request->getHost(), 'http://localhost:3000');
        $this->assertEquals($request->getResource(), '/foo');
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

        $this->assertEquals((string) $request, $expected);
    }
}
