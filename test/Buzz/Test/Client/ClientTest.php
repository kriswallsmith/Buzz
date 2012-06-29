<?php

namespace Buzz\Test\Client;

use Buzz\Message;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideInvalidHosts
     */
    public function testSendToInvalidUrl($host, $client)
    {
        $this->setExpectedException('Buzz\\Exception\\ClientException');

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
