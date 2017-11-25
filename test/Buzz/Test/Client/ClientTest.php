<?php

namespace Buzz\Test\Client;

use Buzz\Exception\ClientException;
use Buzz\Message;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @dataProvider provideInvalidHosts
     */
    public function testSendToInvalidUrl($host, $client)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Buzz\\Exception\\ClientException');
        } else {
            $this->setExpectedException('Buzz\\Exception\\ClientException');
        }

        $request = new Message\Request();
        $request->fromUrl('http://'.$host.':12345');

        $response = new Message\Response();

        $client = new $client();
        $client->setTimeout(0.05);
        $client->send($request, $response);
    }

    public function provideInvalidHosts()
    {
        return array(
            array('invalid_domain', 'Buzz\\Client\\Curl'),
            array('invalid_domain.buzz', 'Buzz\\Client\\Curl'),

            array('invalid_domain', 'Buzz\\Client\\FileGetContents'),
            array('invalid_domain.buzz', 'Buzz\\Client\\FileGetContents'),
        );
    }
}
