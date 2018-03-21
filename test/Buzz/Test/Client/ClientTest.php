<?php

namespace Buzz\Test\Client;

use Buzz\Client\AbstractClient;
use Buzz\Client\BuzzClientInterface;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Client\MultiCurl;
use Buzz\Exception\ClientException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @dataProvider provideInvalidHosts
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
            array('invalid_domain', Curl::class),
            array('invalid_domain.buzz', Curl::class),

            array('invalid_domain', MultiCurl::class),
            array('invalid_domain.buzz', MultiCurl::class),

            array('invalid_domain', FileGetContents::class),
            array('invalid_domain.buzz', FileGetContents::class),
        );
    }
}
