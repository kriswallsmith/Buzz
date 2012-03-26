<?php

namespace Buzz\Message\Factory;

use Buzz\Message\RequestInterface;

interface FactoryInterface
{
    function createRequest($method = RequestInterface::METHOD_GET, $resource = '/', $host = null);
    function createFormRequest($method = RequestInterface::METHOD_POST, $resource = '/', $host = null);
    function createResponse();
}
