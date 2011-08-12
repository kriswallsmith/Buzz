<?php

namespace Buzz\Client\Mock;

use Buzz\Message;

class FIFOTest extends \PHPUnit_Framework_TestCase
{
    public function testSetsResponseHeadersAndContent()
    {
        $response = new Message\Response();
        $response->addHeader('HTTP/1.0 200 OK');
        $response->setContent('Hello World!');

        $client = new FIFO();
        $client->setQueue(array($response));
        $client->sendToQueue($response);

        $this->assertEquals(2, count($client->getQueue()));

        $request = new Message\Request();
        $response = new Message\Response();
        $client->send($request, $response);

        $this->assertEquals(1, count($client->getQueue()));

        $this->assertEquals(array('HTTP/1.0 200 OK'), $response->getHeaders());
        $this->assertEquals('Hello World!', $response->getContent());
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
