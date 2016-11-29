<?php

namespace Buzz\Client;

use Buzz\Message;

interface AsyncBatchClientInterface extends BatchClientInterface
{
    /**
     * Populates the supplied response with the response for the supplied request.
     *
     * @param Message\Request  $request  A request object
     * @param Message\Response $response A response object
     * @param null|resource $curl curl_multi_init()
     * @param null|callable $callback
     * @param null|array $callbackParameters
     */
# disabled because of https://bugs.php.net/bug.php?id=46705
#    function send(Message\Request $request, Message\Response $response, $curl = null, $callback = null, $callbackParameters = array());
}
