<?php

declare(strict_types=1);

namespace Buzz\Test\Integration\Httplug;

use Buzz\Client\FileGetContents;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;

class FileGetContentsIntegrationTest extends BaseIntegrationTest
{
    protected function createHttpAdapter(): ClientInterface
    {
        return new FileGetContents(new Psr17Factory(), []);
    }
}
