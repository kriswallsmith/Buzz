<?php

namespace Buzz\Message\Factory;

use Buzz\Message\RequestInterface;

interface FactoryInterface
{
    /**
     * @param string      $method   A request method
     * @param string      $resource Some resource
     * @param string|null $host     A request host
     *
     * @return RequestInterface
     */
    public function createRequest($method = RequestInterface::METHOD_GET, $resource = '/', $host = null);

    /**
     * @param string      $method   A request method
     * @param string      $resource Some resource
     * @param string|null $host     A request host
     *
     * @return RequestInterface
     */
    public function createFormRequest($method = RequestInterface::METHOD_POST, $resource = '/', $host = null);

    /**
     * Create a response object
     *
     * @return \Buzz\Message\MessageInterface
     */
    public function createResponse();
}
