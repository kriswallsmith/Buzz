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

    public function testGetDomReturnsADomDocument()
    {
        $response = new Message\Response();
        $response->setContent('<html><head></head><body></body></html>');
        $this->browser->getClient()->sendToQueue($response);

        $this->browser->get('http://www.google.com');
        $this->assertTrue($this->browser->getDom() instanceof \DOMDocument);
    }

    public function testBrowserAwareRequest()
    {
        $response = new Message\Response();
        $response->setContent('<html><head></head><body></body></html>');
        $this->browser->getClient()->sendToQueue($response);

        $request = new RequestForTest();
        $this->browser->send($request);
        $this->assertSame($this->browser, $request->browser);
    }
}

class RequestForTest extends Message\Request implements BrowserAwareInterface
{
    public $browser;

    public function setBrowser(Browser $browser)
    {
        $this->browser = $browser;
    }
}
