<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use Buzz\Client\MultiCurl;
use Buzz\Exception\NetworkException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class MultiCurlTest extends TestCase
{
    protected function setUp()
    {
        if (!isset($_SERVER['BUZZ_TEST_SERVER']) || empty($_SERVER['BUZZ_TEST_SERVER'])) {
            $this->markTestSkipped('The test server is not configured.');
        }
    }

    /**
     * MultiCurl handles all exceptions in the callback.
     */
    public function testNoExceptionThrown()
    {
        $client = new MultiCurl(new Psr17Factory(), ['timeout'=>1]);

        $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER'].'?delay=3');

        $client->sendAsyncRequest($request, ['callback' => function($request, $response, $exception) {
            $this->assertInstanceOf(NetworkException::class, $exception);
        }]);

        $client->flush();
    }
}