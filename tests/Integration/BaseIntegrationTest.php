<?php

namespace Buzz\Test\Integration;

use Http\Client\Tests\HttpClientTest;

abstract class BaseIntegrationTest extends HttpClientTest
{
    public function testSendWithInvalidUri()
    {
        $this->markTestSkipped('We do not support HTTPlugs exceptions');
    }
}
