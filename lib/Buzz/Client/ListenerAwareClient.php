<?php

namespace Buzz\Client;

use Buzz\Listener\ListenerInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

class ListenerAwareClient implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ListenerInterface[]
     */
    private $listeners;

    public function __construct(ClientInterface $client, array $listeners =  array())
    {
        $this->client = $client;
        $this->listeners = $listeners;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function addListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request, MessageInterface $response)
    {
        foreach ($this->listeners as $listener) {
            $listener->preSend($request);
        }

        $this->client->send($request, $response);

        foreach ($this->listeners as $listener) {
            $listener->postSend($request, $response);
        }

        return $response;
    }
}
