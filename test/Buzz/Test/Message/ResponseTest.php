<?php

namespace Buzz\Test\Message;

use Buzz\Message\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testGetProtocolVersionReturnsTheProtocolVersion()
    {
        $response = new Response();

        $this->assertNull($response->getProtocolVersion());

        $response->addHeader('HTTP/1.0 200 OK');

        $this->assertEquals('1.0', $response->getProtocolVersion());
    }

    public function testGetStatusCodeReturnsTheStatusCode()
    {
        $response = new Response();

        $this->assertNull($response->getStatusCode());

        $response->addHeader('HTTP/1.0 200 OK');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testGetReasonPhraseReturnsTheReasonPhrase()
    {
        $response = new Response();

        $this->assertEquals($response->getReasonPhrase(), null);

        $response->addHeader('HTTP/1.0 200 OK');

        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function testGetReasonPhraseReturnsAMultiwordReasonPhrase()
    {
        $response = new Response();

        $this->assertNull($response->getReasonPhrase());

        $response->addHeader('HTTP/1.0 500 Internal Server Error');

        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());
    }

    public function testAddHeaderWithoutReasonPhrase()
    {
        $response = new Response();

        $response->addHeader('HTTP/1.0 200');

        $this->assertSame('1.0', $response->getProtocolVersion());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNull($response->getReasonPhrase());
    }

    public function testAddHeadersResetsStatusLine()
    {
        $response = new Response();
        $this->assertNull($response->getStatusCode());
        $response->addHeaders(array('HTTP/1.0 200 OK'));
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @dataProvider statusProvider
     *
     * @param int $code
     * @param string $method
     * @param boolean $expected
     */
    public function testIssers($code, $method, $expected)
    {
        $response = new Response();
        $response->addHeaders(array('HTTP/1.0 '.$code.' Status'));
        $this->assertEquals($expected, $response->{$method}());
    }

    /**
     * @return array
     */
    public function statusProvider()
    {
        return array(
            array(50, 'isInvalid', true),
            array(700, 'isInvalid', true),
            array(Response::HTTP_CONTINUE, 'isInvalid', false),

            array(Response::HTTP_CONTINUE, 'isInformational', true),
            array(199, 'isInformational', true),
            array(Response::HTTP_OK, 'isInformational', false),

            array(Response::HTTP_OK, 'isSuccessful', true),
            array(299, 'isSuccessful', true),
            array(Response::HTTP_MULTIPLE_CHOICES, 'isSuccessful', false),

            array(Response::HTTP_MOVED_PERMANENTLY, 'isRedirection', true),
            array(Response::HTTP_FOUND, 'isRedirection', true),
            array(Response::HTTP_BAD_REQUEST, 'isRedirection', false),

            array(Response::HTTP_NOT_FOUND, 'isClientError', true),
            array(Response::HTTP_UNAUTHORIZED, 'isClientError', true),
            array(Response::HTTP_INTERNAL_SERVER_ERROR, 'isClientError', false),

            array(Response::HTTP_INTERNAL_SERVER_ERROR, 'isServerError', true),
            array(Response::HTTP_BAD_REQUEST, 'isServerError', false),

            array(Response::HTTP_OK, 'isOk', true),
            array(Response::HTTP_CREATED, 'isOk', false),

            array(Response::HTTP_FORBIDDEN, 'isForbidden', true),
            array(Response::HTTP_NOT_FOUND, 'isForbidden', false),

            array(Response::HTTP_NOT_FOUND, 'isNotFound', true),
            array(Response::HTTP_FORBIDDEN, 'isNotFound', false),

            array(Response::HTTP_CREATED, 'isEmpty', true),
            array(Response::HTTP_NO_CONTENT, 'isEmpty', true),
            array(Response::HTTP_NOT_MODIFIED, 'isEmpty', true),
            array(Response::HTTP_NON_AUTHORITATIVE_INFORMATION, 'isEmpty', false),
        );
    }
}
