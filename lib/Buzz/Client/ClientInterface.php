<?php

namespace Buzz\Client;

use Buzz\Message;

interface ClientInterface
{
    /**
     * Populates the supplied response with the response for the supplied request.
     * 
     * @param Message\Request  $request  A request object
     * @param Message\Response $response A response object
     */
    function send(Message\Request $request, Message\Response $response);
}
