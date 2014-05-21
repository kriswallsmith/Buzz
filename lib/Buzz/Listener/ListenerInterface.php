<?php

namespace Buzz\Listener;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

interface ListenerInterface
{
    /**
     * @param RequestInterface $request A request object
     */
    public function preSend(RequestInterface $request);

    /**
     * @param RequestInterface $request  A request object
     * @param MessageInterface $response A response object
     */
    public function postSend(RequestInterface $request, MessageInterface $response);
}
