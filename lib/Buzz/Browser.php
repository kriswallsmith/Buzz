<?php

namespace Buzz;

use Buzz\Client;
use Buzz\Listener;
use Buzz\Message;
use Buzz\Message\Factory;
use Buzz\Util;

class Browser
{
    private $client;
    private $factory;
    private $listener;
    private $lastRequest;
    private $lastResponse;

    public function __construct(Client\ClientInterface $client = null, Factory\FactoryInterface $factory = null)
    {
        $this->client = $client ?: new Client\FileGetContents();
        $this->factory = $factory ?: new Factory\Factory();
    }

    public function get($url, $headers = array())
    {
        return $this->call($url, Message\RequestInterface::METHOD_GET, $headers);
    }

    public function post($url, $headers = array(), $content = '')
    {
        return $this->call($url, Message\RequestInterface::METHOD_POST, $headers, $content);
    }

    public function head($url, $headers = array())
    {
        return $this->call($url, Message\RequestInterface::METHOD_HEAD, $headers);
    }

    public function patch($url, $headers = array(), $content = '')
    {
        return $this->call($url, Message\RequestInterface::METHOD_PATCH, $headers, $content);
    }

    public function put($url, $headers = array(), $content = '')
    {
        return $this->call($url, Message\RequestInterface::METHOD_PUT, $headers, $content);
    }

    public function delete($url, $headers = array(), $content = '')
    {
        return $this->call($url, Message\RequestInterface::METHOD_DELETE, $headers, $content);
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
        $request = $this->factory->createRequest($method);

        if (!$url instanceof Util\Url) {
            $url = new Util\Url($url);
        }

        $url->applyToRequest($request);

        foreach ($headers as $header) {
            $request->addHeader($header);
        }

        $request->setContent($content);

        return $this->send($request);
    }

    /**
     * Sends a form request.
     *
     * @param string $url     The URL to submit to
     * @param array  $fields  An array of fields
     * @param string $method  The request method to use
     * @param array  $headers An array of request headers
     *
     * @return Message\Response The response object
     */
    public function submit($url, array $fields, $method = Message\RequestInterface::METHOD_POST, $headers = array())
    {
        $request = $this->factory->createFormRequest();

        if (!$url instanceof Util\Url) {
            $url = new Util\Url($url);
        }

        $url->applyToRequest($request);

        foreach ($headers as $header) {
            $request->addHeader($header);
        }

        $request->setMethod($method);
        $request->setFields($fields);

        return $this->send($request);
    }

    /**
     * Sends a request.
     *
     * @param Message\RequestInterface $request  A request object
     * @param Message\MessageInterface $response A response object
     *
     * @return Message\MessageInterface The response
     */
    public function send(Message\RequestInterface $request, Message\MessageInterface $response = null)
    {
        if (null === $response) {
            $response = $this->factory->createResponse();
        }

        if ($this->listener) {
            $this->listener->preSend($request);
        }

        $this->client->send($request, $response);

        $this->lastRequest = $request;
        $this->lastResponse = $response;

        if ($this->listener) {
            $this->listener->postSend($request, $response);
        }

        return $response;
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function setClient(Client\ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setMessageFactory(Factory\FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function getMessageFactory()
    {
        return $this->factory;
    }

    public function setListener(Listener\ListenerInterface $listener)
    {
        $this->listener = $listener;
    }

    public function getListener()
    {
        return $this->listener;
    }

    public function addListener(Listener\ListenerInterface $listener)
    {
        if (!$this->listener) {
            $this->listener = $listener;
        } elseif ($this->listener instanceof Listener\ListenerChain) {
            $this->listener->addListener($listener);
        } else {
            $this->listener = new Listener\ListenerChain(array(
                $this->listener,
                $listener,
            ));
        }
    }
}
