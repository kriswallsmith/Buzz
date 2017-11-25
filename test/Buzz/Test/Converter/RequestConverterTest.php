<?php

namespace Buzz\Test\Converter;

use Buzz\Converter\RequestConverter;
use Buzz\Message\Request;
use PHPUnit\Framework\TestCase;

class RequestConverterTest extends TestCase
{
    public function testPsr7()
    {
        $buzz = new Request(Request::METHOD_GET, '/foo', 'https://example.com');
        $buzz->setContent('Foobar');

        $psr = RequestConverter::psr7($buzz);
        $this->assertEquals('GET', $psr->getMethod());
        $this->assertEquals('/foo', $psr->getUri()->getPath());
        $this->assertEquals('https://example.com/foo', $psr->getUri()->__toString());
        $this->assertEquals('Foobar', $psr->getBody()->__toString());
    }
    public function testBuzz()
    {
        $psr = new \GuzzleHttp\Psr7\Request('GET', 'https://example.com/foo?bar', ['header'=>'value'], 'Foobar');

        $buzz = RequestConverter::buzz($psr);
        $this->assertEquals('GET', $buzz->getMethod());
        $this->assertEquals('/foo?bar', $buzz->getResource());
        $this->assertEquals('https://example.com', $buzz->getHost());
        $this->assertEquals('Foobar', $buzz->getContent());
    }
}