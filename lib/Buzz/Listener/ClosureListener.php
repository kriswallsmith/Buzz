<?php

namespace Buzz\Listener;

use Buzz\Message;

class ClosureListener implements ListenerInterface
{
    private $closure;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function preSend(Message\Request $request)
    {
        call_user_func($this->closure, $request);
    }

    public function postSend(Message\Request $request, Message\Response $response)
    {
        call_user_func($this->closure, $request, $response);
    }
}
