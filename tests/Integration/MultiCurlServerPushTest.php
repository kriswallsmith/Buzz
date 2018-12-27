<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use Buzz\Client\MultiCurl;
use Buzz\Exception\NetworkException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class MultiCurlServerPushTest extends TestCase
{
    protected function setUp()
    {
        if (PHP_VERSION_ID < 70400 || curl_version()['version_number'] < 472065) {
            $this->markTestSkipped('This environment does not support server push');
        }
    }

    public function testServerPush()
    {
        $client = new MultiCurl(new Psr17Factory(), ['timeout' => 1]);

        $start = microtime(true);
        $client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush', [], null, '2.0'));
        $timeFirstRequest = microtime(true)-$start;

        // TODO parse request

        $start = microtime(true);
        $client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush/static/style.css?1545951414399773323'));
        $client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush/static/jquery.min.js?1545951414399773323'));
        $timeOtherRequests = microtime(true)-$start;


        var_dump("\n\n\n\nFirst: ".$timeFirstRequest. "\nOther: ".$timeOtherRequests. "\n\n");

        $this->assertTrue($timeFirstRequest > $timeOtherRequests);
        $this->assertFalse(true, "First: ".$timeFirstRequest. "\nOther: ".$timeOtherRequests. "\n");

    }

}
