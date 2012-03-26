<?php

namespace Buzz\Listener\History;

use Buzz\Message;

class Entry
{
    private $request;
    private $response;
    private $duration;

    /**
     * Constructor.
     *
     * @param Message\RequestInterface $request  The request
     * @param Message\MessageInterface $response The response
     * @param integer                  $duration The duration in seconds
     */
    public function __construct(Message\RequestInterface $request, Message\MessageInterface $response, $duration = null)
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
