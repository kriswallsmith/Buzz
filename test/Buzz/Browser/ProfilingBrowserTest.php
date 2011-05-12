<?php

namespace Buzz\Browser;

use Buzz\Client;
use Buzz\Message;

class ProfilingBrowserTest extends \PHPUnit_Framework_TestCase
{
    protected $browser;

    public function setUp()
    {
        $this->browser = new ProfilingBrowser(new ClientForTest());
    }

    public function testTime()
    {
        $this->browser->get('http://example.org/');
        $this->assertGreaterThanOrEqual(2, $this->browser->getJournal()->getLast()->getTime());
    }
}

class ClientForTest implements Client\ClientInterface {

    public function send(Message\Request $request, Message\Response $response)
    {
        usleep(2000);
    }
}
