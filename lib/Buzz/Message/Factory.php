<?php

namespace Buzz\Message;

class Factory implements FactoryInterface
{
    public function createRequest($method = Request::METHOD_GET, $resource = '/', $host = null)
    {
        return new Request($method, $resource, $host);
    }

    public function createResponse()
    {
        return new Response();
    }
}
