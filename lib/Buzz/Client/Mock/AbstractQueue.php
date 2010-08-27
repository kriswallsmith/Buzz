<?php

namespace Buzz\Client\Mock;

use Buzz\Client;
use Buzz\Message;

abstract class AbstractQueue implements Client\ClientInterface
{
    protected $queue = array();
    protected $sendCallable;
    protected $receiveCallable;

    /**
     * Constructor.
     * 
     * @param mixed $sendCallable    Called when sending a response to the queue
     * @param mixed $receiveCallable Called when receiving a response from the queue
     */
    public function __construct($sendCallable, $receiveCallable)
    {
        $this->sendCallable = $sendCallable;
        $this->receiveCallable = $receiveCallable;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function setQueue(array $queue)
    {
        foreach ($queue as $response) {
            $this->sendToQueue($response);
        }
    }

    /**
     * Sends a response into the queue.
     * 
     * @param Message\Response $response A response
     */
    public function sendToQueue(Message\Response $response)
    {
        call_user_func($this->sendCallable, &$this->queue, $response);
    }

    /**
     * Receives a response from the queue.
     * 
     * @return Message\Response|null
     */
    public function receiveFromQueue()
    {
        if (count($this->queue)) {
            return call_user_func($this->receiveCallable, &$this->queue);
        }
    }

    /**
     * @see Client\ClientInterface
     */
    public function send(Message\Request $request, Message\Response $response)
    {
        if (!$queued = $this->receiveFromQueue()) {
            throw new \LogicException('There are no queued responses.');
        }

        $response->setHeaders($queued->getHeaders());
        $response->setContent($queued->getContent());
    }
}
