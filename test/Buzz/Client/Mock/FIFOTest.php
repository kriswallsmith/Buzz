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

        $this->assertEquals(count($client->getQueue()), 2);

        $request = new Message\Request();
        $response = new Message\Response();
        $client->send($request, $response);

        $this->assertEquals(count($client->getQueue()), 1);

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
