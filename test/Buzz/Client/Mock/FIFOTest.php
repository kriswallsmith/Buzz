<?php

namespace Buzz\Client\Mock;

use Buzz\Message;

require_once __DIR__.'/../../../../lib/Buzz/ClassLoader.php';
\Buzz\ClassLoader::register();

require_once 'PHPUnit/Framework/TestCase.php';

class FIFOTest extends \PHPUnit_Framework_TestCase
{
  public function testSetsResponseHeadersAndContent()
  {
    $response = new Message\Response();
    $response->addHeader('HTTP/1.0 200 OK');
    $response->setContent('Hello World!');

    $client = new FIFO();
    $client->sendToQueue($response);

    $request = new Message\Request();
    $response = new Message\Response();
    $client->send($request, $response);

    $this->assertEquals($response->getHeaders(), array('HTTP/1.0 200 OK'));
    $this->assertEquals($response->getContent(), 'Hello World!');
  }

  /**
   * @expectedException LogicException
   */
  public function testThrowsAnExceptionIfTheQueueIsEmpty()
  {
    $request = new Message\Request();
    $response = new Message\Response();

    $client = new FIFO();
    $client->send($request, $response);
  }
}
