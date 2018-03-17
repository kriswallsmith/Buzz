<?php

namespace Buzz\Test\Client;

use Buzz\Client\AbstractClient;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @dataProvider provideInvalidHosts
     * @group legacy
     */
    public function testSendToInvalidUrl($host, $client)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Buzz\\Exception\\ClientException');
        } else {
            $this->setExpectedException('Buzz\\Exception\\ClientException');
        }

        $request = new Request('GET', 'http://'.$host.':12345');

        /** @var AbstractClient $client */
        $client = new $client();
        $client->setTimeout(0.05);
        $client->sendRequest($request);
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
