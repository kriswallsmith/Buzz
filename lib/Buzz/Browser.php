<?php

namespace Buzz;

use Buzz\Client\ClientInterface;
use Buzz\Client\FileGetContents;
use Buzz\Message\Factory\Factory;
use Buzz\Message\Factory\FactoryInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Util\Url;

class Browser
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var FactoryInterface
     */
    private $factory;

    public function __construct(ClientInterface $client = null, FactoryInterface $factory = null)
    {
        $this->client = $client ?: new FileGetContents();
        $this->factory = $factory ?: new Factory();
    }

    public function get($url, $headers = array())
    {
        return $this->call($url, RequestInterface::METHOD_GET, $headers);
    }

    public function post($url, $headers = array(), $content = '')
    {
        return $this->call($url, RequestInterface::METHOD_POST, $headers, $content);
    }

    public function head($url, $headers = array())
    {
        return $this->call($url, RequestInterface::METHOD_HEAD, $headers);
    }

    public function patch($url, $headers = array(), $content = '')
    {
        return $this->call($url, RequestInterface::METHOD_PATCH, $headers, $content);
    }

    public function put($url, $headers = array(), $content = '')
    {
        return $this->call($url, RequestInterface::METHOD_PUT, $headers, $content);
    }

    public function delete($url, $headers = array(), $content = '')
    {
        return $this->call($url, RequestInterface::METHOD_DELETE, $headers, $content);
    }

    /**
     * Sends a request.
     *
     * @param string $url     The URL to call
     * @param string $method  The request method to use
     * @param array  $headers An array of request headers
     * @param string $content The request content
     *
     * @return MessageInterface The response object
     */
    public function call($url, $method, array $headers = array(), $content = '')
    {
        $request = $this->factory->createRequest($method);
        $request->setContent($content);

        return $this->send($request, $url, $headers);
    }

    /**
     * Sends a form request.
     *
     * @param string $url     The URL to submit to
     * @param array  $fields  An array of fields
     * @param string $method  The request method to use
     * @param array  $headers An array of request headers
     *
     * @return MessageInterface The response object
     */
    public function submit($url, array $fields, $method = RequestInterface::METHOD_POST, array $headers = array())
    {
        $request = $this->factory->createFormRequest($method);
        $request->setFields($fields);

        return $this->send($request, $url, $headers);
    }

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setMessageFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function getMessageFactory()
    {
        return $this->factory;
    }

    /**
     * @param RequestInterface $request
     * @param string|Url       $url
     * @param array|null       $headers
     *
     * @return MessageInterface
     */
    private function send(RequestInterface $request, $url, array $headers = null)
    {
        if (!$url instanceof Url) {
            $url = new Url($url);
        }
        $url->applyToRequest($request);

        if ($headers) {
            $request->addHeaders($headers);
        }

        $response = $this->factory->createResponse();
        $this->client->send($request, $response);

        return $response;
    }
}
