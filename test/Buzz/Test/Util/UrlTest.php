<?php

namespace Buzz\Test\Util;

use Buzz\Util\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
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
        $this->setExpectedException('InvalidArgumentException');
        new Url('http://localhost:123456');
    }
}
