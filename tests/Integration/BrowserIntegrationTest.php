<?php

namespace Buzz\Test\Integration;

use Buzz\Browser;
use Buzz\Client\FileGetContents;

class BrowserIntegrationTest extends BaseIntegrationTest
{
    protected function createHttpAdapter()
    {
        $client = new FileGetContents();
        $browser = new Browser($client);

        return $browser;
    }
}
