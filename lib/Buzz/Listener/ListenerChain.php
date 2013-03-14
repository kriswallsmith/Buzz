<?php

namespace Buzz\Listener;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

class ListenerChain implements ListenerInterface
{
    private $listeners;

    /**
     * @param ListenerInterface[] $listeners
     */
    public function __construct(array $listeners = array())
    {
        $this->listeners = $listeners;
    }

    /**
     * @param ListenerInterface $listener
     */
    public function addListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @return ListenerInterface[]
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * {@inheritDoc}
     */
    public function preSend(RequestInterface $request)
    {
        foreach ($this->listeners as $listener) {
            $listener->preSend($request);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function postSend(RequestInterface $request, MessageInterface $response)
    {
        foreach ($this->listeners as $listener) {
            $listener->postSend($request, $response);
        }
    }
}
