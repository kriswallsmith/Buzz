<?php

namespace Buzz\Client;

use Buzz\Message\RequestInterface;
use Buzz\Message\MessageInterface;

abstract class AbstractDecorator implements ClientInterface
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    abstract public function preSend(RequestInterface $request);

    public function send(RequestInterface $request, MessageInterface $response)
    {
        $this->preSend($request);
        $this->client->send($request, $response);
        $this->postSend($request, $response);
    }

    public function resend(RequestInterface $request, MessageInterface $response)
    {
        $this->client->send($request, $response);
    }

    abstract public function postSend(RequestInterface $request, MessageInterface $response);
}