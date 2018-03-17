<?php

namespace Buzz\Test\Client;

use Buzz\Client\AbstractStream;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class StreamClient extends AbstractStream
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
    }
}

class AbstractStreamTest extends TestCase
{
    public function testConvertsARequestToAContextArray()
    {
        $request = new Request('POST', 'http://example.com/resource/123', [
            'Content-Type'=>'application/x-www-form-urlencoded',
            'Content-Length'=> '15',
        ], 'foo=bar&bar=baz');

        $client = new StreamClient();
        $client->setMaxRedirects(5);
        $client->setIgnoreErrors(false);
        $client->setTimeout(10);
        $expected = array(
            'http' => array(
                'method'           => 'POST',
                'header'           => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: 15",
                'content'          => 'foo=bar&bar=baz',
                'protocol_version' => 1.1,
                'ignore_errors'    => false,
                'follow_location'  => true,
                'max_redirects'    => 6,
                'timeout'          => 10,
            ),
            'ssl' => array(
                'verify_peer'      => true,
                'verify_host' => 2,
            ),
        );

        $this->assertEquals($expected, $client->getStreamContextArray($request));

        $client->setVerifyPeer(true);
        $expected['ssl']['verify_peer'] = true;
        $this->assertEquals($expected, $client->getStreamContextArray($request));

        $client->setMaxRedirects(0);
        $expected['http']['follow_location'] = false;
        $expected['http']['max_redirects'] = 1;
        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }
}
