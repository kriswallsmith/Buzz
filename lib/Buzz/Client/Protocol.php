<?php

namespace Buzz\Client;

use Buzz\Message;
use Samson\Protocol\Protocol\HTTP;
class Protocol extends AbstractClient implements ClientInterface
{
    private $http;

    public function __construct()
    {
        $this->http = new HTTP();
    }

    public function send(Message\Request $request, Message\Response $response)
    {
        $response->fromString($this->http->request($request->getUrl(), $request->getMethod(), $request->getHeaders(), $request->getContent()));
    }

}
