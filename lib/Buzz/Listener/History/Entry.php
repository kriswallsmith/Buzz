<?php

namespace Buzz\Listener\History;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

class Entry
{
    private $request;
    private $response;
    private $duration;

    /**
     * Constructor.
     *
     * @param RequestInterface $request  The request
     * @param MessageInterface $response The response
     * @param integer          $duration The duration in seconds
     */
    public function __construct(RequestInterface $request, MessageInterface $response, $duration = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->duration = $duration;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return MessageInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return int|null
     */
    public function getDuration()
    {
        return $this->duration;
    }
}
