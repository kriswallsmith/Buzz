<?php

namespace Buzz\Test\Client;

use Buzz\Client\AbstractStream;
use Buzz\Client\FileGetContents;
use Buzz\Configuration\ParameterBag;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
            'Content-Type'=>'application/x-www-form-urlencoded',
            'Content-Length'=> '15',
        ], 'foo=bar&bar=baz');

        $client = new StreamClient();
        $expected = array(
            'http' => array(
                'method'           => 'POST',
                'header'           => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: 15",
                'content'          => 'foo=bar&bar=baz',
                'protocol_version' => '1.1',
                'ignore_errors'    => true,
                'follow_location'  => true,
                'max_redirects'    => 6,
                'timeout'          => 10,
            ),
            'ssl' => array(
                'verify_peer'      => true,
                'verify_host' => 2,
            ),
        );

        $options = new ParameterBag([
            'max_redirects' => 5,
            'timeout' => 10,
            'follow_redirects' => true,
            'verify_peer' => true,
            'verify_host' => true,
        ]);
        $this->assertEquals($expected, $client->getStreamContextArray($request, $options));

        $options = $options->add(['verify_peer'=>false]);
        $expected['ssl']['verify_peer'] = false;
        $this->assertEquals($expected, $client->getStreamContextArray($request, $options));

        $options = $options->add(['max_redirects'=>0]);
        $expected['http']['follow_location'] = false;
        $expected['http']['max_redirects'] = 1;
        $this->assertEquals($expected, $client->getStreamContextArray($request, $options));
    }
}
