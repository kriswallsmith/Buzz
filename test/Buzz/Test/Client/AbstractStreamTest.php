<?php

namespace Buzz\Test\Client;

use Buzz\Client\AbstractStream;
use Buzz\Message;

class StreamClient extends AbstractStream
{
}

class AbstractStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertsRequestToContextArray()
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

        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }

    public function testSettingProxy()
    {
        $request = new Message\Request('GET', '/resource/123', 'http://example.com');

        $client = new StreamClient();
        $client->setProxyIp('127.0.0.1', '3128');

        $expected = array(
            'http' => array(
                'method'           => 'GET',
                'header'           => '',
                'content'          => '',
                'protocol_version' => 1.0,
                'ignore_errors'    => true,
                'max_redirects'    => 5,
                'timeout'          => 5,
                'proxy'            => 'tcp://127.0.0.1:3128',
                'request_fulluri'  => true,
            )
        );

        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }

    public function testSettingProxyAuth()
    {
        $request = new Message\Request('GET', '/resource/123', 'http://example.com');

        $client = new StreamClient();
        $client->setProxyIp('127.0.0.1');
        $client->setProxyAuth('kris', 'sirk');

        $expected = array(
            'http' => array(
                'method'           => 'GET',
                'header'           => '',
                'content'          => '',
                'protocol_version' => 1.0,
                'ignore_errors'    => true,
                'max_redirects'    => 5,
                'timeout'          => 5,
                'proxy'            => 'tcp://kris:sirk@127.0.0.1',
                'request_fulluri'  => true,
            )
        );

        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }
}