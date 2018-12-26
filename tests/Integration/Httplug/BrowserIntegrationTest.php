<?php

declare(strict_types=1);

namespace Buzz\Test\Integration\Httplug;

use Buzz\Browser;
use Buzz\Client\FileGetContents;
use Nyholm\Psr7\Factory\Psr17Factory;

class BrowserIntegrationTest extends BaseIntegrationTest
{
    protected function createHttpAdapter()
    {
        $client = new FileGetContents(new Psr17Factory(), []);
        $browser = new Browser($client, new Psr17Factory());

        return $browser;
    }
}
