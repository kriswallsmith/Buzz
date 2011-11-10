<?php

namespace Buzz;

use Buzz\Client\Mock;
use Buzz\Message;

class BrowserTest extends \PHPUnit_Framework_TestCase
{
    protected $browser;

    public function setUp()
    {
        $this->browser = new Browser(new Mock\LIFO());
    }

    public function testGetReturnsAResponse()
    {
        $this->browser->getClient()->sendToQueue(new Message\Response());
        $this->assertInstanceOf('Buzz\\Message\\Response', $this->browser->get('http://www.google.com'));
    }
}
