<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Client;

use Buzz\Client\BuzzClientInterface;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Client\MultiCurl;
use Buzz\Exception\ClientException;
use Nyholm\Psr7\Factory\Psr17Factory;
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
        $client = new $client([], new Psr17Factory());
        $client->sendRequest($request, ['timeout' => 0.1]);
    }

    public function provideInvalidHosts()
    {
        return [
            ['invalid_domain', Curl::class],
            ['invalid_domain.buzz', Curl::class],

            ['invalid_domain', MultiCurl::class],
            ['invalid_domain.buzz', MultiCurl::class],

            ['invalid_domain', FileGetContents::class],
            ['invalid_domain.buzz', FileGetContents::class],
        ];
    }
}
