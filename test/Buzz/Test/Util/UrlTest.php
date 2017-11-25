<?php

namespace Buzz\Test\Util;

use Buzz\Exception\InvalidArgumentException;
use Buzz\Util\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    /**
     * @dataProvider provideUrlAndHost
     */
    public function testGetHost($urlStr, $host, $resource)
    {
        $url = new Url($urlStr);
        $this->assertEquals($host, $url->getHost());
        $this->assertEquals($resource, $url->getResource());
    }

    public function provideUrlAndHost()
    {
        return array(
            array('https://example.com/resource/123?foo=bar#foobar', 'https://example.com', '/resource/123?foo=bar'),
            array('http://example.com', 'http://example.com', '/'),
            array('http://example.com?foo=bar', 'http://example.com', '/?foo=bar'),
            array('example.com/foo/bar', 'http://example.com', '/foo/bar'),
            array('/foo', null, '/foo'),
            array('http://localhost:3000/foo', 'http://localhost:3000', '/foo'),
        );
    }

    public function testInvalidUrl()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->setExpectedException(InvalidArgumentException::class);
        }

        new Url('http://localhost:123456');
    }

    /**
     * @dataProvider providePattern
     */
    public function testFormat($input, $pattern, $expected)
    {
        $url = new Url($input);
        $this->assertEquals($expected, $url->format($pattern));
    }

    public function providePattern()
    {
        $full = 'http://foo:bar@example.com:123/asdf?tweedle=dee#dum';
        $host = 'example.com';

        return array(
            array($full, 's://u:a@h:op?q#f', 'http://foo:bar@example.com:123/asdf?tweedle=dee#dum'),
            array($full, 'HR', 'http://example.com:123/asdf?tweedle=dee'),
            array($host, 'HR', 'http://example.com/'),
        );
    }
}
