<?php

declare(strict_types=1);

namespace Buzz\Middleware\History;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Entry
{
    private $request;
    private $response;
    private $duration;

    /**
     * Constructor.
     *
     * @param RequestInterface  $request  The request
     * @param ResponseInterface $response The response
     * @param int               $duration The duration in seconds
     */
    public function __construct(RequestInterface $request, ResponseInterface $response, $duration = null)
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
