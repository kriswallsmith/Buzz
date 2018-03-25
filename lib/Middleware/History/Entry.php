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
     * @param RequestInterface  $request  The request
     * @param ResponseInterface $response The response
     * @param null|float        $duration The duration in seconds
     */
    public function __construct(RequestInterface $request, ResponseInterface $response, float $duration = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->duration = $duration;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }
}
