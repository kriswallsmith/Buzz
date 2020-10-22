<?php

declare(strict_types=1);

namespace Buzz\Test\Integration\Httplug;

use Http\Client\Tests\HttpClientTest;
use Http\Client\Tests\PHPUnitUtility;

abstract class BaseIntegrationTest extends HttpClientTest
{
    protected function setUp(): void
    {
        parent::setUp();
        if (false === PHPUnitUtility::getUri()) {
            $this->markTestSkipped('No URL provided');
        }
    }
}
