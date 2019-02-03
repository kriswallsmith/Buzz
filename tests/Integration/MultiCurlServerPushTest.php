<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use Buzz\Client\MultiCurl;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;

class MultiCurlServerPushTest extends BaseIntegrationTest
{
    protected function setUp()
    {
        parent::setUp();
        if (\PHP_VERSION_ID < 70400 || curl_version()['version_number'] < 472065) {
            $this->markTestSkipped('This environment does not support server push');
        }
    }

    public function testServerPush()
    {
        $client = new MultiCurl(new Psr17Factory());

        $start = microtime(true);
        $response = $client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush', [], null, '2.0'));
        $timeFirstRequest = microtime(true) - $start;

        $body = $response->getBody()->__toString();
        $id = null;
        if (preg_match('#/serverpush/static/style.css\?([0-9]+)#sim', $body, $matches)) {
            $id = $matches[1];
        }

        $this->assertNotNull($id, 'We could not parse request');

        $start = microtime(true);
        $client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush/static/style.css?'.$id));
        $client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush/static/playground.js?'.$id));
        $timeOtherRequests = microtime(true) - $start;

        $this->assertTrue($timeFirstRequest > $timeOtherRequests, 'First: '.$timeFirstRequest."\nOther: ".$timeOtherRequests."\n");
    }
}
