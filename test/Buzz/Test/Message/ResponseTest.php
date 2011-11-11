<?php

namespace Buzz\Test\Message;

use Buzz\Message\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProtocolVersionReturnsTheProtocolVersion()
    {
        $response = new Response();

        $this->assertNull($response->getProtocolVersion());

        $response->addHeader('1.0 200 OK');

        $this->assertEquals(1.0, $response->getProtocolVersion());
    }

    public function testGetStatusCodeReturnsTheStatusCode()
    {
        $response = new Response();

        $this->assertNull($response->getStatusCode());

        $response->addHeader('1.0 200 OK');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetReasonPhraseReturnsTheReasonPhrase()
    {
        $response = new Response();

        $this->assertEquals($response->getReasonPhrase(), null);

        $response->addHeader('1.0 200 OK');

        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function testGetReasonPhraseReturnsAMultiwordReasonPhrase()
    {
        $response = new Response();

        $this->assertNull($response->getReasonPhrase());

        $response->addHeader('1.0 500 Internal Server Error');

        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());
    }

    public function testFromString()
    {
        $content = <<<EOF
This is the body.

More body!

EOF;
        $response = new Response();
        $response->fromString(<<<EOF
HTTP/1.0 200 OK
Content-Type: text/plain

$content
EOF
        );

        $this->assertEquals(2, count($response->getHeaders()));
        $this->assertEquals($content, $response->getContent());
    }

    public function testAddHeadersResetsStatusLine()
    {
        $response = new Response();
        $this->assertNull($response->getStatusCode());
        $response->addHeaders(array('1.0 200 OK'));
        $this->assertEquals(200, $response->getStatusCode());
    }
}
