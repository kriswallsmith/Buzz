<?php

namespace Buzz\History;

use Buzz\Message;

class Entry
{
    private $request;
    private $response;
    private $duration;

    /**
     * Constructor.
     *
     * @param Message\Request  $request  The request
     * @param Message\Response $response The response
     * @param integer          $duration The duration in seconds
     */
    public function __construct(Message\Request $request, Message\Response $response, $duration = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->duration = $duration;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getDuration()
    {
        return $this->duration;
    }
}
