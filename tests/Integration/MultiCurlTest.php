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
        if (empty($_SERVER['BUZZ_TEST_SERVER'])) {
            $this->markTestSkipped('The test server is not configured.');
        }
    }

    /**
     * MultiCurl handles all exceptions in the callback.
     */
    public function testNoExceptionThrown()
    {
        $client = new MultiCurl(new Psr17Factory(), ['timeout' => 1]);

        $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER'].'?delay=3');

        $client->sendAsyncRequest($request, ['callback' => function ($request, $response, $exception) {
            $this->assertInstanceOf(NetworkException::class, $exception);
        }]);

        $client->flush();
    }

    public function testProceed()
    {
        $client = new MultiCurl(new Psr17Factory(), ['timeout' => 3]);

        $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER'].'?delay=1');

        $returnResponse = null;
        $client->sendAsyncRequest($request, ['callback' => function ($request, $response, $e) use (&$returnResponse) {
            $returnResponse = $response;
        }]);

        $i = 0;
        while (true) {
            $client->proceed();
            if (null !== $returnResponse) {
                // Make sure $client->proceed() is non-blocking
                $this->assertTrue($i > 0, 'MultiCurl::proceed() must be non-blocking.');

                return;
            }
            ++$i;
        }
    }
}
