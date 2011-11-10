<?php

namespace Buzz;

use Buzz\Client;
use Buzz\Message;

class Browser
{
    private $client;
    private $factory;

    public function __construct(Client\ClientInterface $client = null, Message\FactoryInterface $factory = null)
    {
        $this->client = $client ?: new Client\FileGetContents();
        $this->factory = $factory ?: new Message\Factory();
    }

    public function get($url, $headers = array())
    {
        return $this->call($url, Message\Request::METHOD_GET, $headers);
    }

    public function post($url, $headers = array(), $content = '')
    {
        return $this->call($url, Message\Request::METHOD_POST, $headers, $content);
    }

    public function head($url, $headers = array())
    {
        return $this->call($url, Message\Request::METHOD_HEAD, $headers);
    }

    public function put($url, $headers = array(), $content = '')
    {
        return $this->call($url, Message\Request::METHOD_PUT, $headers, $content);
    }

    public function delete($url, $headers = array(), $content = '')
    {
        return $this->call($url, Message\Request::METHOD_DELETE, $headers, $content);
    }

    /**
     * Sends a request.
     * 
     * @param string $url     The URL to call
     * @param string $method  The request method to use
     * @param array  $headers An array of request headers
     * @param string $content The request content
     * 
     * @return Message\Response The response object
     */
    public function call($url, $method, $headers = array(), $content = '')
    {
        $request = $this->factory->createRequest();

        $request->setMethod($method);
        $request->fromUrl($url);
        $request->addHeaders($headers);
        $request->setContent($content);

        return $this->send($request);
    }

    /**
     * Sends a request.
     * 
     * @param Message\Request  $request  A request object
     * @param Message\Response $response A response object
     * 
     * @return Message\Response A response object
     */
    public function send(Message\Request $request, Message\Response $response = null)
    {
        if (null === $response) {
            $response = $this->factory->createResponse();
        }

        $this->client->send($request, $response);

        return $response;
    }

    public function setClient(Client\ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setMessageFactory(Message\FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function getMessageFactory()
    {
        return $this->factory;
    }
}
