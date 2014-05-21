<?php

namespace Buzz\Message\Factory;

use Buzz\Message\Form\FormRequest;
use Buzz\Message\Request;
use Buzz\Message\RequestInterface;
use Buzz\Message\Response;

class Factory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return Request
     */
    public function createRequest($method = RequestInterface::METHOD_GET, $resource = '/', $host = null)
    {
        return new Request($method, $resource, $host);
    }

    /**
     * {@inheritDoc}
     *
     * @return FormRequest
     */
    public function createFormRequest($method = RequestInterface::METHOD_POST, $resource = '/', $host = null)
    {
        return new FormRequest($method, $resource, $host);
    }

    /**
     * {@inheritDoc}
     *
     * @return Response
     */
    public function createResponse()
    {
        return new Response();
    }
}
