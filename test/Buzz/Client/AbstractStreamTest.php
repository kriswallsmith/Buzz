<?php

namespace Buzz\Client;

use Buzz\Message;

class StreamClient extends AbstractStream
{
}

class AbstractStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertsARequestToAContextArray()
    {
        $request = new Message\Request('POST', '/resource/123', 'http://example.com');
        $request->addHeader('Content-Type: application/x-www-form-urlencoded');
        $request->addHeader('Content-Length: 15');
        $request->setContent('foo=bar&bar=baz');

        $client = new StreamClient();
        $client->setMaxRedirects(5);
        $client->setIgnoreErrors(false);
        $client->setTimeout(10);
        $expected = array('http' => array(
            'method'           => 'POST',
            'header'           => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: 15",
            'content'          => 'foo=bar&bar=baz',
            'protocol_version' => 1.0,
            'ignore_errors'    => false,
            'max_redirects'    => 5,
            'timeout'          => 10,
        ));

        $this->assertEquals($client->getStreamContextArray($request), $expected);
    }

    public function testSettingBasicAuthData()
    {
        $request = new Message\Request('GET', '/resource/123', 'http://example.com');

        $client = new StreamClient();
        $client->setAuth('kris', 'sirk');

        $expected = array(
            'http' => array(
                'method'           => 'GET',
                'header'           => 'Authorization: Basic a3JpczpzaXJr',
                'content'          => '',
                'protocol_version' => 1.0,
                'ignore_errors'    => true,
                'max_redirects'    => 5,
                'timeout'          => 5,
            )
        );

        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }
}
