<?php

declare(strict_types=1);

namespace Buzz\Test\Integration\Httplug;

use Buzz\Client\FileGetContents;
use Nyholm\Psr7\Factory\Psr17Factory;

class FileGetContentsIntegrationTest extends BaseIntegrationTest
{
    protected function createHttpAdapter()
    {
        return new FileGetContents(new Psr17Factory(), []);
    }
}
