<?php

namespace Buzz\History;

use Buzz\Message;

class Entry
{
    private $request;
    private $response;

    public function __construct(Message\Request $request, Message\Response $response)
    {
        $this->setRequest($request);
        $this->setResponse($response);
    }

    public function setRequest(Message\Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setResponse(Message\Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
