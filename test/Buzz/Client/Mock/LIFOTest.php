<?php

namespace Buzz\Client\Mock;

use Buzz\Message;

class LIFOTest extends \PHPUnit_Framework_TestCase
{
    public function testUsesTheLastQueuedResponse()
    {
        $response1 = new Message\Response();
        $response1->setContent('first');

        $response2 = new Message\Response();
        $response2->setContent('last');

        $client = new LIFO();
        $client->sendToQueue($response1);
        $client->sendToQueue($response2);

        $request = new Message\Request();
        $response = new Message\Response();
        $client->send($request, $response);

        $this->assertEquals('last', $response->getContent());
    }
}
