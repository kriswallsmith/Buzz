<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use Buzz\Client\FileGetContents;

class FileGetContentsIntegrationTest extends BaseIntegrationTest
{
    protected function createHttpAdapter()
    {
        $client = new FileGetContents();

        return $client;
    }
}
