<?php

namespace Buzz\Listener;

use Buzz\Message;

interface ListenerInterface
{
    function preSend(Message\Request $request);
    function postSend(Message\Request $request, Message\Response $response);
}
