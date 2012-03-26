<?php

namespace Buzz\Listener;

use Buzz\Message;

interface ListenerInterface
{
    function preSend(Message\RequestInterface $request);
    function postSend(Message\RequestInterface $request, Message\MessageInterface $response);
}
