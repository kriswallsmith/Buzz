<?php

namespace Buzz;

use Buzz;
use Buzz\Client\Mock;
use Buzz\Message;

require_once __DIR__.'/../../lib/Buzz/ClassLoader.php';
Buzz\ClassLoader::register();

require_once 'PHPUnit/Framework/TestCase.php';

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
    $this->assertTrue($this->browser->get('http://www.google.com') instanceof Message\Response);
  }

  public function testGetDomReturnsADomDocument()
  {
    $response = new Message\Response();
    $response->setContent('<html><head></head><body></body></html>');
    $this->browser->getClient()->sendToQueue($response);
    $this->browser->get('http://www.google.com');
    $this->assertTrue($this->browser->getDom() instanceof \DOMDocument);
  }
}
