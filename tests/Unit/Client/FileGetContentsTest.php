<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Client;

use Buzz\Client\FileGetContents;
use Buzz\Configuration\ParameterBag;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class StreamClient extends FileGetContents
{
    public function getStreamContextArray(RequestInterface $request, ParameterBag $options): array
    {
        return parent::getStreamContextArray($request, $options);
    }
}

class FileGetContentsTest extends TestCase
{
    public function testConvertsARequestToAContextArray()
    {
        $request = new Request('POST', 'http://example.com/resource/123', [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Content-Length' => '15',
        ], 'foo=bar&bar=baz');

        $client = new StreamClient([], new Psr17Factory());
        $expected = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: 15",
                'content' => 'foo=bar&bar=baz',
                'protocol_version' => '1.1',
                'ignore_errors' => true,
                'follow_location' => true,
                'max_redirects' => 6,
                'timeout' => 10,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_host' => 2,
            ],
        ];

        $options = new ParameterBag([
            'max_redirects' => 5,
            'timeout' => 10,
            'allow_redirects' => true,
            'verify' => true,
        ]);
        $this->assertEquals($expected, $client->getStreamContextArray($request, $options));

        $options = $options->add(['verify' => false]);
        $expected['ssl']['verify_peer'] = false;
        $expected['ssl']['verify_host'] = false;
        $this->assertEquals($expected, $client->getStreamContextArray($request, $options));

        $options = $options->add(['max_redirects' => 0]);
        $expected['http']['follow_location'] = false;
        $expected['http']['max_redirects'] = 1;
        $this->assertEquals($expected, $client->getStreamContextArray($request, $options));
    }
}
