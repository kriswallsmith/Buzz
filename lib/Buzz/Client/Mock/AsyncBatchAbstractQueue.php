<?php

namespace Buzz\Client\Mock;

use Buzz\Client;
use Buzz\Message;

abstract class AsyncBatchAbstractQueue extends AbstractQueue implements Client\AsyncBatchClientInterface
{
    public function sendToQueue(Message\Response $response, $callNum = 0)
    {
        $this->queue[$callNum][] = array('response' => $response);
    }

    public function flush()
    {
        $queued = $this->receiveFromQueue();

        foreach ($queued as $item) {
            if (empty($item['callback'])) {
                throw new \LogicException('Queued item has not yet been send.');
            }

            \call_user_func_array($item['callback'], array_merge(array($item['response']), $item['callbackParameters']));
        }
    }

    public function send(Message\Request $request, Message\Response $response, $curl = null, $callback = null, $callbackParameters = array())
    {
        if (!$queued = $this->getFromQueue()) {
            throw new \LogicException('There are no queued responses.');
        }

        $response->setHeaders($queued['response']->getHeaders());
        $response->setContent($queued['response']->getContent());
        $queued['response'] = $response;
        $queued['callback'] = $callback;
        $queued['callbackParameters'] = $callbackParameters;

        $this->updateInQueue($queued);
    }
}
