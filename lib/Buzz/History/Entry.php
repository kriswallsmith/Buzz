<?php

namespace Buzz\History;

use Buzz\Message;

class Entry
{
    protected $request;
    protected $response;
    protected $time;

    public function __construct(Message\Request $request, Message\Response $response, $time = 0)
    {
        $this->setRequest($request);
        $this->setResponse($response);
        $this->time = $time;
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

    public function getTime() {
        return $this->time;
    }
}
