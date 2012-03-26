<?php

namespace Buzz\Client;

use Buzz\Message;

interface ClientInterface
{
    /**
     * Populates the supplied response with the response for the supplied request.
     *
     * @param Message\RequestInterface $request  A request object
     * @param Message\MessageInterface $response A response object
     */
    function send(Message\RequestInterface $request, Message\MessageInterface $response);
}
