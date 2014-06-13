<?php

namespace Buzz\Client;

use Buzz\Message\RequestInterface;
use Buzz\Message\MessageInterface;

abstract class AbstractDecoratorClient implements ClientInterface
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Called immediately before the call to send()
     *
     * @param RequestInterface $request  A request object
     * @param MessageInterface $response A response object
     */
    abstract public function preSend(RequestInterface $request);

    /**
     * Calls preSend(). Calls send() on the client object we have decorated. Calls postSend().
     *
     * @param RequestInterface $request  A request object
     * @param MessageInterface $response A response object
     */
    public function send(RequestInterface $request, MessageInterface $response)
    {
        $this->preSend($request);
        $this->client->send($request, $response);
        $this->postSend($request, $response);
    }

    /**
     * Calls send() on the client object we have decorated.
     *
     * @param RequestInterface $request  A request object
     * @param MessageInterface $response A response object
     */
    public function resend(RequestInterface $request, MessageInterface $response)
    {
        $this->client->send($request, $response);
    }

    /**
     * Called immediately after the call to send() has returned from the decorated client object.
     *
     * @param RequestInterface $request  A request object
     * @param MessageInterface $response A response object
     */
    abstract public function postSend(RequestInterface $request, MessageInterface $response);
}