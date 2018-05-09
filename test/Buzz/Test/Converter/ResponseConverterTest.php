<?php

namespace Buzz\Test\Converter;

use Buzz\Converter\ResponseConverter;
use Buzz\Message\Response;
use PHPUnit\Framework\TestCase;

class ResponseConverterTest extends TestCase
{
    public function testPsr7()
    {
        $buzz = new Response();
        $buzz->addHeader('HTTP/1.0 200 OK');
        $buzz->addHeader('Content-Type: text/html');
        $buzz->setContent('Foobar');

        $psr = ResponseConverter::psr7($buzz);
        $this->assertEquals('1.0', $psr->getProtocolVersion());
        $this->assertEquals('OK', $psr->getReasonPhrase());
        $this->assertEquals('200', $psr->getStatusCode());
        $this->assertEquals('Foobar', $psr->getBody()->__toString());
        $this->assertEquals('text/html', $psr->getHeaderLine('Content-Type'));
    }


    public function testBuzz()
    {
        $psr = new \GuzzleHttp\Psr7\Response(
            200,
            ['content-type'=>'text/html'],
            'Foobar',
            '1.1',
            'OK'
        );

        $buzz = ResponseConverter::buzz($psr);
        $this->assertEquals('200', $buzz->getStatusCode());
        $this->assertEquals('1.1', $buzz->getProtocolVersion());
        $this->assertEquals('OK', $buzz->getReasonPhrase());
        $this->assertEquals('Foobar', $buzz->getContent());
        $this->assertEquals('text/html', $buzz->getHeader('Content-type'));
    }
}
