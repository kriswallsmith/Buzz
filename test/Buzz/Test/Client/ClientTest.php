<?php

namespace Buzz\Test\Client;

use Buzz\Client\AbstractClient;
use Buzz\Client\BuzzClientInterface;
use Buzz\Exception\ClientException;
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
        $this->expectException(ClientException::class);

        $request = new Request('GET', 'http://'.$host.':12345');

        /** @var BuzzClientInterface $client */
        $client = new $client();
        $client->sendRequest($request, ['timeout'=>0.1]);
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
