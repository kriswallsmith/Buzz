<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use Buzz\Client\FileGetContents;
use Nyholm\Psr7\Factory\Psr17Factory;

class FileGetContentsIntegrationTest extends BaseIntegrationTest
{
    protected function createHttpAdapter()
    {
        $client = new FileGetContents([], new Psr17Factory());

        return $client;
    }
}
