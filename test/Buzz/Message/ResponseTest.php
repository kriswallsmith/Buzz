<?php

namespace Buzz\Message;

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
}
