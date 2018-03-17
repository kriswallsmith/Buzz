<?php

namespace Buzz;

use Buzz\Client\BatchClientInterface;
use Buzz\Client\ClientInterface;
use Buzz\Client\FileGetContents;
use Buzz\Converter\RequestConverter;
use Buzz\Converter\ResponseConverter;
use Buzz\Listener\ListenerChain;
use Buzz\Listener\ListenerInterface;
use Buzz\Message\Factory\Factory;
use Buzz\Message\Factory\FactoryInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Middleware\MiddlewareInterface;
use Buzz\Util\Url;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\RequestInterface as Psr7RequestInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;
use Psr\Http\Message\UriInterface;

class Browser
{
    /** @var ClientInterface */
    private $client;

    /** @var FactoryInterface */
    private $factory;

    /** @var ListenerInterface */
    private $listener;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /** @var RequestInterface */
    private $lastRequest;

    /** @var MessageInterface */
    private $lastResponse;

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
    public function call($url, $method, $headers = array(), $content = '')
    {
        $request = $this->factory->createRequest($method);

        if (!$url instanceof Url) {
            $url = new Url($url);
        }

        $url->applyToRequest($request);

        $request->addHeaders($headers);
        $request->setContent($content);

        $psr7Request = RequestConverter::psr7($request);
        $psr7Response = $this->sendRequest($psr7Request);

        return ResponseConverter::buzz($psr7Response);
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
     * @deprecated Will be removed in version 1.0. Use submitForm instead.
     */
    public function submit($url, array $fields, $method = RequestInterface::METHOD_POST, $headers = array())
    {
        @trigger_error('Broswer::send() is deprecated. Use Broswer::submitForm instead.', E_USER_DEPRECATED);
        $request = $this->factory->createFormRequest();

        if (!$url instanceof Url) {
            $url = new Url($url);
        }

        $url->applyToRequest($request);

        $request->addHeaders($headers);
        $request->setMethod($method);
        $request->setFields($fields);

        return $this->send($request);
    }

    /**
     * Sends a request.
     *
     * @param RequestInterface $request  A request object
     * @param MessageInterface $response A response object
     *
     * @return MessageInterface The response
     * @deprecated Will be removed in version 1.0. Use sendRequest instead.
     */
    public function send(RequestInterface $request, MessageInterface $response = null)
    {
        @trigger_error('Broswer::send() is deprecated. Use Broswer::sendRequest instead.', E_USER_DEPRECATED);
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

    /**
     * @param string|UriInterface $url
     * @param array $fields
     * @param string $method
     * @param array $headers
     *
     * @return Psr7ResponseInterface
     */
    public function submitForm($url, array $fields, $method = RequestInterface::METHOD_POST, $headers = array())
    {
        $body = [];
        $files = '';
        $boundary = uniqid('', true);
        foreach ($fields as $name => $field) {
            if (!isset($field['path'])) {
                 $body[$name] = $field;
            } else {
                // This is a file
                $fileContent = file_get_contents($field['path']);
                $files .= $this->prepareMultipart($name, $fileContent, $boundary, $field);
            }
        }

        if (empty($files)) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $body = http_build_query($body);
        } else {
            $headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;

            foreach ($body as $name => $value) {
                $files .= $this->prepareMultipart($name, $value, $boundary);
            }
            $body = "$files--{$boundary}--\r\n";
        }

        $request = new Request($method, $url, $headers, $body);

        return $this->sendRequest($request);
    }

    /**
     * Send a PSR7 request.
     *
     * @param Psr7RequestInterface $request
     * @return Psr7ResponseInterface
     */
    public function sendRequest(Psr7RequestInterface $request)
    {
        $chain = $this->createMiddlewareChain($this->middlewares, function(Psr7RequestInterface $request, callable $responseChain) {
            if ($this->client instanceof BatchClientInterface) {
                $this->client->sendRequest($request, ['callback' => function(BatchClientInterface $client, $request, $response, $options, $result) use ($responseChain) {
                    return $responseChain($request, $response);
                }]);
            } else {
                $response = $this->client->sendRequest($request);
                $responseChain($request, $response);
            }
        }, function (Psr7RequestInterface $request, Psr7ResponseInterface $response) {
            $this->lastRequest = $request;
            $this->lastResponse = $response;
        });

        // Call the chain
        $chain($request);

        return $this->lastResponse;
    }

    /**
     * @param MiddlewareInterface[] $middlewares
     * @param callable $requestChainLast
     * @param callable $responseChainLast
     *
     * @return callable
     */
    private function createMiddlewareChain(array $middlewares, callable $requestChainLast, callable $responseChainLast)
    {
        $responseChainNext = $responseChainLast;

        // Build response chain
        /** @var MiddlewareInterface $middleware */
        foreach ($middlewares as $middleware) {
            $lastCallable = function (Psr7RequestInterface $request, Psr7ResponseInterface $response) use ($middleware, $responseChainNext) {
                return $middleware->handleResponse($request, $response, $responseChainNext);
            };

            $responseChainNext = $lastCallable;
        }

        $requestChainLast = function (Psr7RequestInterface $request) use ($requestChainLast, $responseChainNext) {
            // Send the actual request and get the response
            $requestChainLast($request, $responseChainNext);
        };

        $middlewares = array_reverse($middlewares);

        // Build request chain
        $requestChainNext = $requestChainLast;
        /** @var MiddlewareInterface $middleware */
        foreach ($middlewares as $middleware) {
            $lastCallable = function (Psr7RequestInterface $request) use ($middleware, $requestChainNext) {
                return $middleware->handleRequest($request, $requestChainNext);
            };

            $requestChainNext = $lastCallable;
        }

        return $requestChainNext;
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
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

    public function setListener(ListenerInterface $listener)
    {
        $this->listener = $listener;
    }

    public function getListener()
    {
        return $this->listener;
    }

    /**
     * Add a new middleware to the stack.
     *
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function addListener(ListenerInterface $listener)
    {
        if (!$this->listener) {
            $this->listener = $listener;
        } elseif ($this->listener instanceof ListenerChain) {
            $this->listener->addListener($listener);
        } else {
            $this->listener = new ListenerChain(array(
                $this->listener,
                $listener,
            ));
        }
    }

    /**
     * @param $name
     * @param $content
     * @param $boundary
     * @param array $data
     * @return string
     */
    private function prepareMultipart($name, $content, $boundary, array $data = [])
    {
        $output = '';
        $fileHeaders = [];

        // Set a default content-disposition header
        $fileHeaders['Content-Disposition'] = sprintf('form-data; name="%s"', $name);
        if (isset($data['filename'])) {
            $fileHeaders['Content-Disposition'] .= sprintf('; filename="%s"', $data['filename']);
        }

        // Set a default content-length header
        if ($length = strlen($content)) {
            $fileHeaders['Content-Length'] = (string)$length;
        }

        if (isset($data['contentType'])) {
            $fileHeaders['Content-Type'] = $data['contentType'];
        }

        // Add start
        $output .= "--$boundary\r\n";
        foreach ($fileHeaders as $key => $value) {
            $output .= sprintf("%s: %s\r\n", $key, $value);
        }
        $output .= "\r\n";
        $output .= $content;
        $output .= "\r\n";

        return $output;
    }
}
