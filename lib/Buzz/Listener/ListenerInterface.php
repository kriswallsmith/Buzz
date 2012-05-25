<?php

namespace Buzz\Listener;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

interface ListenerInterface
{
    function preSend(RequestInterface $request);
    function postSend(RequestInterface $request, MessageInterface $response);
}
