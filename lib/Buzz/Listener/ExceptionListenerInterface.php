<?php

namespace Buzz\Listener;

use Buzz\Listener\ListenerInterface;
use Buzz\Message\RequestInterface;

interface ExceptionListenerInterface extends ListenerInterface
{
    public function onException(RequestInterface $request, \Exception $exception);
}
