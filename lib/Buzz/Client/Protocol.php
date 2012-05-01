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
        $this->http->setTimeout($this->getTimeout());
        $this->http->setMaxRedirects($this->getMaxRedirects());

        $raw = $this->http->request($request->getUrl(), $request->getMethod(), $request->getHeader
        list($headersRaw, $content) = explode("\r\n\r\n", $raw, 2);
        $response->setContent($content);
        $response->setHeaders(explode("\r\n", $headersRaw));
    }
}
