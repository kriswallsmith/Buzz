<?php

namespace Buzz;

use Buzz\Client\BatchClientInterface;
use Buzz\Client\FileGetContents;
use Buzz\Middleware\MiddlewareInterface;
use Nyholm\Psr7\Factory\MessageFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Browser
{
    /** @var ClientInterface */
    private $client;

    /** @var MessageFactory */
    private $factory;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /** @var RequestInterface */
    private $lastRequest;

    /** @var ResponseInterface */
    private $lastResponse;

    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client ?: new FileGetContents();
        $this->factory = new MessageFactory();
    }

    public function get($url, $headers = array())
    {
        return $this->call($url, 'GET', $headers);
    }

    public function post($url, $headers = array(), $content = '')
    {
        return $this->call($url, 'POST', $headers, $content);
    }

    public function head($url, $headers = array())
    {
        return $this->call($url, 'HEAD', $headers);
    }

    public function patch($url, $headers = array(), $content = '')
    {
        return $this->call($url, 'PATCH', $headers, $content);
    }

    public function put($url, $headers = array(), $content = '')
    {
        return $this->call($url, 'PUT', $headers, $content);
    }

    public function delete($url, $headers = array(), $content = '')
    {
        return $this->call($url, 'DELETE', $headers, $content);
    }

    /**
     * Sends a request.
     *
     * @param string $url     The URL to call
     * @param string $method  The request method to use
     * @param array  $headers An array of request headers
     * @param string $body The request content
     *
     * @return ResponseInterface The response object
     */
    public function call(string $url, string $method, array $headers = array(), string $body = ''): ResponseInterface
    {
        $request = $this->factory->createRequest($method, $url, $headers, $body);

        return $this->sendRequest($request);
    }


    /**
     * @param string|UriInterface $url
     * @param array $fields
     * @param string $method
     * @param array $headers
     *
     * @return ResponseInterface
     */
    public function submitForm(string $url, array $fields, string $method = 'POST', array $headers = array()): ResponseInterface
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

        $request = $this->factory->createRequest($method, $url, $headers, $body);

        return $this->sendRequest($request);
    }

    /**
     * Send a PSR7 request.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $chain = $this->createMiddlewareChain($this->middlewares, function(RequestInterface $request, callable $responseChain) {
            if ($this->client instanceof BatchClientInterface) {
                $this->client->sendRequest($request, ['callback' => function(BatchClientInterface $client, $request, $response, $options, $result) use ($responseChain) {
                    return $responseChain($request, $response);
                }]);
            } else {
                $response = $this->client->sendRequest($request);
                $responseChain($request, $response);
            }
        }, function (RequestInterface $request, ResponseInterface $response) {
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
            $lastCallable = function (RequestInterface $request, ResponseInterface $response) use ($middleware, $responseChainNext) {
                return $middleware->handleResponse($request, $response, $responseChainNext);
            };

            $responseChainNext = $lastCallable;
        }

        $requestChainLast = function (RequestInterface $request) use ($requestChainLast, $responseChainNext) {
            // Send the actual request and get the response
            $requestChainLast($request, $responseChainNext);
        };

        $middlewares = array_reverse($middlewares);

        // Build request chain
        $requestChainNext = $requestChainLast;
        /** @var MiddlewareInterface $middleware */
        foreach ($middlewares as $middleware) {
            $lastCallable = function (RequestInterface $request) use ($middleware, $requestChainNext) {
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

    /**
     * Add a new middleware to the stack.
     *
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    private function prepareMultipart(string $name, string $content, string $boundary, array $data = []): string
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
