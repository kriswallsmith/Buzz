<?php

namespace Buzz\Message;

interface FactoryInterface
{
    function createRequest($method = Request::METHOD_GET, $resource = '/', $host = null);
    function createResponse();
}
