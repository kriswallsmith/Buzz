<?php

namespace Buzz\Message;

interface FactoryInterface
{
    function createRequest($method = Request::METHOD_GET, $resource = '/', $host = null);
    function createFormRequest($method = Request::METHOD_POST, $resource = '/', $host = null);
    function createResponse();
}
