<?php

namespace Buzz\Listener;

use Buzz\Message;

class ListenerChain implements ListenerInterface
{
    private $listeners;

    public function __construct(array $listeners = array())
    {
        $this->listeners = $listeners;
    }

    public function addListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    public function getListeners()
    {
        return $this->listeners;
    }

    public function preSend(Message\RequestInterface $request)
    {
        foreach ($this->listeners as $listener) {
            $listener->preSend($request);
        }
    }

    public function postSend(Message\RequestInterface $request, Message\MessageInterface $response)
    {
        foreach ($this->listeners as $listener) {
            $listener->postSend($request, $response);
        }
    }
}
