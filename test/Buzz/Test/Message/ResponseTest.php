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

    public function testAddHeadersResetsStatusLine()
    {
        $response = new Response();
        $this->assertNull($response->getStatusCode());
        $response->addHeaders(array('1.0 200 OK'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @dataProvider statusProvider
     *
     *
     */
    public function testIssers($code, $method, $expected)
    {
        $response = new Response();
        $response->addHeaders(array('1.0 '.$code.' Status'));
        $this->assertEquals($expected, $response->{$method}());
    }

    public function statusProvider()
    {
        return array(
            array(50, 'isInvalid', true),
            array(700, 'isInvalid', true),
            array(100, 'isInvalid', false),

            array(100, 'isInformational', true),
            array(199, 'isInformational', true),
            array(200, 'isInformational', false),

            array(200, 'isSuccessful', true),
            array(299, 'isSuccessful', true),
            array(300, 'isSuccessful', false),

            array(301, 'isRedirection', true),
            array(302, 'isRedirection', true),
            array(400, 'isRedirection', false),

            array(404, 'isClientError', true),
            array(401, 'isClientError', true),
            array(500, 'isClientError', false),

            array(500, 'isServerError', true),
            array(400, 'isServerError', false),

            array(200, 'isOk', true),
            array(201, 'isOk', false),

            array(403, 'isForbidden', true),
            array(404, 'isForbidden', false),

            array(404, 'isNotFound', true),
            array(403, 'isNotFound', false),

            array(201, 'isEmpty', true),
            array(204, 'isEmpty', true),
            array(304, 'isEmpty', true),
            array(203, 'isEmpty', false),
        );
    }
}
