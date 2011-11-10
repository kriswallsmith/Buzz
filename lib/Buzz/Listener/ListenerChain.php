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

    public function preSend(Message\Request $request)
    {
        foreach ($this->listeners as $listener) {
            $listener->preSend($request);
        }
    }

    public function postSend(Message\Request $request, Message\Response $response)
    {
        foreach ($this->listeners as $listener) {
            $listener->postSend($request, $response);
        }
    }
}
